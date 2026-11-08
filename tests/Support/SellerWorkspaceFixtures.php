<?php

namespace Tests\Support;

use App\Models\Buyer;
use App\Models\Seller;
use App\Models\User;
use App\Models\Seller\Manage_inventory\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

trait SellerWorkspaceFixtures
{
    use RefreshDatabase;

    private function user(string $role = 'seller', array $extra = []): User
    {
        return User::forceCreate(array_replace(['email' => uniqid($role).'@sync.test',
            'password' => 'OriginalPassword123!', 'account_type' => $role,
            'status' => 'approved', 'email_verified_at' => now()], $extra));
    }

    private function profile(array $extra = []): array
    {
        return array_replace(['first_name' => 'Maria', 'last_name' => 'Santos', 'middle_initial' => null,
            'sex' => 'Female', 'contact_no' => '09171234567', 'birthday' => '1992-06-17'], $extra);
    }

    private function seller(): User
    {
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        $user = $this->user();
        Seller::create($this->profile() + ['user_id' => $user->id,
            'province_code' => '04', 'province_name' => 'Laguna',
            'municipality_code' => '0410', 'municipality_name' => 'Calamba',
            'barangay_code' => '041001', 'barangay_name' => 'Real', 'street_address' => '1 Test Street',
            'business_name' => 'Test Shop', 'business_category' => 'Home and Garden',
            'valid_id_path' => 'private/id.pdf', 'business_permit_path' => 'private/permit.pdf']);
        return $user;
    }

    private function buyer(): User
    {
        $user = $this->user('buyer');
        Buyer::create($this->profile() + ['user_id' => $user->id,
            'province_code' => '04', 'province_name' => 'Laguna',
            'municipality_code' => '0410', 'municipality_name' => 'Calamba',
            'barangay_code' => '041001', 'barangay_name' => 'Real', 'street_address' => '1 Buyer Street',
            'valid_id_path' => 'private/buyer.pdf']);
        return $user;
    }

    private function product(User $seller, array $extra = []): Product
    {
        return Product::create(array_replace(['seller_id' => $seller->id, 'name' => 'Test Lamp',
            'category' => 'Home and Garden', 'subcategory' => 'Furniture & Decor',
            'price' => 100, 'weight_kg' => 1, 'stock' => 10,
            'status' => 'active', 'compliance_status' => 'approved'], $extra));
    }

    private function productInput(array $extra = []): array
    {
        return array_replace(['name' => 'New Lamp', 'subcategory' => 'Furniture & Decor',
            'price' => 200, 'weight_kg' => 1, 'stock' => 5, 'has_variants' => '0'], $extra);
    }
}
