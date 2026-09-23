<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LogisticsConsoleAccessBoundaryTest extends TestCase
{
    private int $userSequence = 0;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('account_type');
            $table->string('status');
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function test_guest_cannot_render_the_logistics_console_dashboard(): void
    {
        $response = $this->get(route('logistics.dashboard'));

        self::assertSame(302, $response->getStatusCode());
        self::assertSame(route('login'), $response->headers->get('Location'));
    }

    public function test_logistics_application_remains_public(): void
    {
        $response = $this->get(route('logistics.register'));

        self::assertSame(200, $response->getStatusCode());
    }

    public function test_approved_logistics_operator_can_reach_representative_console_pages(): void
    {
        $operator = $this->makeUser('logistics');

        $dashboard = $this->actingAs($operator)
            ->get(route('logistics.dashboard'));

        self::assertSame(200, $dashboard->getStatusCode());

        $riders = $this->actingAs($operator)
            ->get(route('logistics.riders.index'));

        self::assertSame(200, $riders->getStatusCode());
    }

    public function test_other_approved_account_types_cannot_render_the_logistics_console(): void
    {
        foreach (['buyer', 'seller', 'admin'] as $accountType) {
            $response = $this->actingAs($this->makeUser($accountType))
                ->get(route('logistics.dashboard'));

            self::assertSame(403, $response->getStatusCode(), $accountType);
        }
    }

    private function makeUser(string $accountType): User
    {
        $this->userSequence++;

        $user = new User();
        $user->forceFill([
            'name' => ucfirst($accountType),
            'email' => "{$accountType}-{$this->userSequence}@example.test",
            'password' => 'not-used-by-this-test',
            'account_type' => $accountType,
            'status' => 'approved',
            'email_verified_at' => now(),
        ])->save();

        return $user;
    }
}
