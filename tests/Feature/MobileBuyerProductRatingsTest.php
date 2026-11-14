<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\MobileBuyerOrderController;
use App\Models\Buyer;
use App\Models\Buyer\Review\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class MobileBuyerProductRatingsTest extends TestCase
{
    private User $buyerUser;

    private array $temporaryMedia = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('sqlite', DB::getDefaultConnection());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        Schema::create('buyers', fn (Blueprint $t) => $t->id());
        Schema::create('products', function (Blueprint $t) {
            $t->id();
            $t->string('name');
        });
        Schema::create('product_images', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('product_id');
        });
        Schema::create('orders', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('buyer_id');
            $t->unsignedBigInteger('seller_id')->nullable();
            $t->string('status');
            $t->string('payment_method')->default('cod');
            $t->timestamps();
        });
        Schema::create('order_items', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('order_id');
            $t->unsignedBigInteger('product_id');
            $t->unsignedBigInteger('product_variant_id')->nullable();
            $t->integer('quantity')->default(1);
            $t->decimal('price')->default(10);
        });
        foreach (['deliveries', 'return_cases'] as $table) {
            Schema::create($table, function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('order_id');
            });
        }
        (require database_path('migrations/Buyer/2026_09_12_102229_create_reviews_table.php'))->up();
        (require database_path('migrations/2026_11_13_000001_add_media_to_reviews.php'))->up();
        DB::table('buyers')->insert([['id' => 1], ['id' => 2]]);
        DB::table('products')->insert([['id' => 1, 'name' => 'Purchased item'], ['id' => 2, 'name' => 'Other item']]);
        foreach ([[1, 1, 'completed', 1], [2, 1, 'completed', 1], [3, 1, 'to-receive', 2], [4, 2, 'completed', 2]] as [$id, $buyer, $status, $product]) {
            DB::table('orders')->insert(['id' => $id, 'buyer_id' => $buyer, 'status' => $status]);
            DB::table('order_items')->insert(['id' => $id, 'order_id' => $id, 'product_id' => $product]);
        }
        $this->buyerUser = new User;
        $this->buyerUser->forceFill(['account_type' => 'buyer', 'status' => 'approved', 'email_verified_at' => now()]);
        $this->buyerUser->setRelation('buyer', Buyer::findOrFail(1));
    }

    protected function tearDown(): void
    {
        foreach ($this->temporaryMedia as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }
        parent::tearDown();
    }

    private function request(array $data, string $method = 'POST'): Request
    {
        $request = Request::create('/api/mobile/buyer/orders', $method, $data);
        $request->setUserResolver(fn () => $this->buyerUser);

        return $request;
    }

    public function test_rating_is_saved_to_existing_product_reviews_and_retries_are_idempotent(): void
    {
        $controller = new MobileBuyerOrderController;
        $before = $controller->index($this->request(['status_group' => 'to-rate'], 'GET'))->getData(true)['data'];
        $this->assertSame(2, $before['status_counts']['to-rate']);
        $this->assertCount(2, $before['orders']);
        $this->assertTrue($before['orders'][0]['items'][0]['can_rate']);
        $first = $controller->rateItem($this->request(['rating' => 4, 'comment' => ' Good quality ']), 1, 1)->getData(true);
        $retry = $controller->rateItem($this->request(['rating' => 1, 'comment' => 'Retry']), 2, 2)->getData(true);
        $this->assertSame($first['data']['review_id'], $retry['data']['review_id']);
        $this->assertSame(1, DB::table('reviews')->count());
        $this->assertDatabaseHas('reviews', ['buyer_id' => 1, 'product_id' => 1, 'rating' => 4, 'comment' => 'Good quality', 'variant_label' => 'Standard']);
        $after = $controller->index($this->request(['status_group' => 'to-rate'], 'GET'))->getData(true)['data'];
        $this->assertSame(0, $after['status_counts']['to-rate']);
        $this->assertCount(0, $after['orders']);
        $completed = $controller->index($this->request(['status_group' => 'completed'], 'GET'))->getData(true)['data'];
        $this->assertFalse($completed['orders'][0]['items'][0]['can_rate']);
        $this->assertSame(4, $completed['orders'][0]['items'][0]['rating']);
    }

    public function test_undelivered_orders_and_invalid_ratings_are_rejected_without_writes(): void
    {
        $controller = new MobileBuyerOrderController;
        foreach ([[3, 3, ['rating' => 5]], [1, 1, ['rating' => 0]], [1, 1, ['rating' => 6]], [1, 1, ['rating' => 2, 'comment' => str_repeat('x', 2001)]]] as [$order, $item, $data]) {
            try {
                $controller->rateItem($this->request($data), $order, $item);
                $this->fail('Invalid rating must be rejected.');
            } catch (ValidationException $error) {
                $this->assertNotEmpty($error->errors());
            }
        }
        $this->assertSame(0, DB::table('reviews')->count());
    }

    public function test_foreign_orders_and_items_from_another_order_are_not_rateable(): void
    {
        $controller = new MobileBuyerOrderController;
        foreach ([[4, 4], [1, 2]] as [$order, $item]) {
            try {
                $controller->rateItem($this->request(['rating' => 5]), $order, $item);
                $this->fail('Cross-order rating must be rejected.');
            } catch (ModelNotFoundException) {
                $this->assertSame(0, DB::table('reviews')->count());
            }
        }
    }

    public function test_multiple_photos_and_video_are_stored_once_and_exposed_as_public_media(): void
    {
        Storage::fake('public');
        $request = $this->request(['rating' => 5, 'comment' => 'With photos and video']);
        $request->files->set('images', [$this->photo('one.jpg'), $this->photo('two.png')]);
        $request->files->set('videos', [$this->mp4('demo.mp4')]);
        $controller = new MobileBuyerOrderController;
        $controller->rateItem($request, 1, 1);
        $review = Review::sole();
        $this->assertSame(['image', 'image', 'video'], array_column($review->media, 'type'));
        $this->assertSame($review->media[0]['path'], $review->image_path);
        foreach ($review->media as $media) {
            Storage::disk('public')->assertExists($media['path']);
        }
        $this->assertCount(3, $review->publicMedia());
        $this->assertStringContainsString('/storage/reviews/', $review->publicMedia()[2]['url']);
        $controller->rateItem($request, 1, 1);
        $this->assertSame(1, Review::count());
        $this->assertCount(3, Storage::disk('public')->allFiles());
    }

    public function test_media_limits_invalid_types_and_ineligible_orders_leave_no_uploads(): void
    {
        Storage::fake('public');
        $controller = new MobileBuyerOrderController;
        foreach ([
            ['images', array_map(fn ($i) => $this->photo("image-$i.jpg"), range(1, 7)), 1],
            ['videos', [UploadedFile::fake()->create('large.mp4', 30721, 'video/mp4')], 1],
            ['videos', array_map(fn ($i) => UploadedFile::fake()->create("v-$i.mp4", 1, 'video/mp4'), range(1, 3)), 1],
            ['images', [UploadedFile::fake()->create('unsafe.jpg', 1, 'text/plain')], 1],
            ['images', [$this->photo('valid.jpg')], 3],
            ['images', [$this->photo('unsafe.php')], 1],
            ['images', [UploadedFile::fake()->createWithContent('large.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aX1cAAAAASUVORK5CYII=').str_repeat('x', 5120 * 1024))], 1],
            ['videos', [$this->mp4('unsafe.php')], 1],
        ] as [$field, $files, $order]) {
            $request = $this->request(['rating' => 5]);
            $request->files->set($field, $files);
            try {
                $controller->rateItem($request, $order, $order);
                $this->fail('Invalid attachments must be rejected.');
            } catch (ValidationException $error) {
                $this->assertNotEmpty($error->errors());
            }
        }
        $this->assertCount(0, Storage::disk('public')->allFiles());
        $this->assertSame(0, Review::count());
    }

    public function test_guest_seller_and_unapproved_buyers_cannot_upload_review_media(): void
    {
        Storage::fake('public');
        $request = $this->request(['rating' => 5]);
        $request->files->set('images', [$this->photo('photo.png')]);
        foreach ([null, ['account_type' => 'seller'], ['status' => 'pending'], ['email_verified_at' => null]] as $changes) {
            $user = $changes === null ? null : clone $this->buyerUser;
            if ($user) {
                $user->forceFill($changes);
            }
            $request->setUserResolver(fn () => $user);
            try {
                (new MobileBuyerOrderController)->rateItem($request, 1, 1);
                $this->fail('Unauthorized uploads must be rejected.');
            } catch (\Symfony\Component\HttpKernel\Exception\HttpException $error) {
                $this->assertSame($user ? 403 : 401, $error->getStatusCode());
            }
        }
        $this->assertSame(0, Review::count());
        $this->assertCount(0, Storage::disk('public')->allFiles());
    }

    public function test_legacy_photo_remains_readable_and_media_migration_is_reversible(): void
    {
        $review = Review::create(['buyer_id' => 1, 'product_id' => 1, 'rating' => 4, 'image_path' => 'reviews/legacy.jpg']);
        $this->assertSame('image', $review->publicMedia()[0]['type']);
        $this->assertStringContainsString('reviews/legacy.jpg', $review->publicMedia()[0]['url']);
        $migration = require database_path('migrations/2026_11_13_000001_add_media_to_reviews.php');
        $migration->down();
        $this->assertFalse(Schema::hasColumn('reviews', 'media'));
        $this->assertSame('reviews/legacy.jpg', Review::sole()->image_path);
        $migration->up();
        $this->assertTrue(Schema::hasColumn('reviews', 'media'));
        $this->assertNull(Review::sole()->media);
    }

    public function test_star_only_and_single_photo_reviews_are_saved_without_required_text(): void
    {
        Storage::fake('public');
        $controller = new MobileBuyerOrderController;
        $controller->rateItem($this->request(['rating' => 3]), 1, 1);
        $this->assertNull(Review::sole()->comment);
        $this->assertNull(Review::sole()->image_path);
        Review::query()->delete(); // Only isolated in-memory test records.
        $request = $this->request(['rating' => 5]);
        $request->files->set('images', [$this->photo('photo.png')]);
        $controller->rateItem($request, 1, 1);
        $this->assertCount(1, Review::sole()->publicMedia());
        $this->assertCount(1, Storage::disk('public')->allFiles());
    }

    public function test_maximum_attachment_counts_and_real_mp4_content_are_supported(): void
    {
        Storage::fake('public');
        $request = $this->request(['rating' => 5]);
        $photos = array_map(fn ($i) => $this->photo("photo-$i.png"), range(1, 6));
        $photos[0] = UploadedFile::fake()->createWithContent('boundary.png', str_pad(base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aX1cAAAAASUVORK5CYII='), 5 * 1024 * 1024, "\0"));
        $request->files->set('images', $photos);
        $video = $this->mp4('one.mp4');
        $handle = fopen($video->getPathname(), 'ab');
        ftruncate($handle, 30 * 1024 * 1024);
        fclose($handle);
        $this->assertSame('video/mp4', $video->getMimeType());
        $request->files->set('videos', [$video, $this->mp4('two.mp4')]);
        (new MobileBuyerOrderController)->rateItem($request, 1, 1);
        $this->assertCount(8, Review::sole()->media);
        $this->assertSame(2, collect(Review::sole()->publicMedia())->where('type', 'video')->count());
        $this->assertCount(8, Storage::disk('public')->allFiles());
    }

    public function test_failed_review_save_rolls_back_record_and_removes_its_uploaded_media(): void
    {
        Storage::fake('public');
        $request = $this->request(['rating' => 5]);
        $request->files->set('images', [$this->photo('photo.png')]);
        Review::creating(fn () => throw new \RuntimeException('Simulated database write failure'));
        try {
            (new MobileBuyerOrderController)->rateItem($request, 1, 1);
            $this->fail('A failed save must propagate.');
        } catch (\RuntimeException $error) {
            $this->assertSame('Simulated database write failure', $error->getMessage());
        } finally {
            Review::flushEventListeners();
        }
        $this->assertSame(0, Review::count());
        $this->assertCount(0, Storage::disk('public')->allFiles());
    }

    private function mp4(string $name): UploadedFile
    {
        // ISO base-media ftyp header; server MIME sniffing reads real bytes.
        $path = tempnam(sys_get_temp_dir(), 'review-mp4-');
        $this->temporaryMedia[] = $path;
        file_put_contents($path, hex2bin('00000018667479706d703432000000006d70343269736f6d'));

        return new UploadedFile($path, $name, null, UPLOAD_ERR_OK, true);
    }

    private function photo(string $name): UploadedFile
    {
        // A real 1px PNG fixture avoids requiring the optional GD extension.
        return UploadedFile::fake()->createWithContent($name, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aX1cAAAAASUVORK5CYII='));
    }

    public function test_php_upload_error_has_a_friendly_message_and_never_saves_a_review(): void
    {
        Storage::fake('public');
        $photo = $this->photo('valid.png');
        $failed = new UploadedFile($photo->getPathname(), 'valid.png', 'image/png', UPLOAD_ERR_INI_SIZE, true);
        $request = $this->request(['rating' => 4]);
        $request->files->set('images', [$failed]);
        try {
            (new MobileBuyerOrderController)->rateItem($request, 1, 1);
            $this->fail('A PHP upload failure must not save a review.');
        } catch (ValidationException $error) {
            $message = $error->errors()['images.0'][0];
            $this->assertStringNotContainsString('images.0', $message);
            $this->assertStringContainsString('photo', $message);
        }
        $this->assertSame(0, Review::count());
        $this->assertCount(0, Storage::disk('public')->allFiles());
    }
}
