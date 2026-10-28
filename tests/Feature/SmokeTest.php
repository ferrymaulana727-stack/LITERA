<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_guest_routes_accessible(): void
    {
        $this->get('/catalog')->assertStatus(200);
        $this->get('/login')->assertStatus(200);
        $this->get('/register')->assertStatus(200);
    }

    public function test_customer_routes_accessible(): void
    {
        $customer = User::where('role', 'customer')->first();

        $this->actingAs($customer)->get('/catalog')->assertStatus(200);
        $this->actingAs($customer)->get('/my-orders')->assertStatus(200);
    }

    public function test_staff_routes_accessible(): void
    {
        $staff = User::where('role', 'staff')->first();

        $this->actingAs($staff)->get('/dashboard')->assertStatus(200);
        $this->actingAs($staff)->get('/categories')->assertStatus(200);
        $this->actingAs($staff)->get('/suppliers')->assertStatus(200);
        $this->actingAs($staff)->get('/products')->assertStatus(200);
        $this->actingAs($staff)->get('/purchases')->assertStatus(200);
        $this->actingAs($staff)->get('/purchases/create')->assertStatus(200);
        $this->actingAs($staff)->get('/sales')->assertStatus(200);
        $this->actingAs($staff)->get('/sales/create')->assertStatus(200);
    }

    public function test_admin_routes_accessible(): void
    {
        $admin = User::where('role', 'admin')->first();

        $this->actingAs($admin)->get('/dashboard')->assertStatus(200);
        $this->actingAs($admin)->get('/users')->assertStatus(200);
        $this->actingAs($admin)->get('/users/create')->assertStatus(200);
        $this->actingAs($admin)->get('/categories/create')->assertStatus(200);
        $this->actingAs($admin)->get('/suppliers/create')->assertStatus(200);
        $this->actingAs($admin)->get('/products/create/form')->assertStatus(200);
    }

    public function test_edit_and_show_pages(): void
    {
        $admin = User::where('role', 'admin')->first();
        $product = Product::first();
        $category = Category::first();
        $supplier = Supplier::first();

        if ($product) {
            $this->actingAs($admin)->get("/products/{$product->id}")->assertStatus(200);
            $this->actingAs($admin)->get("/products/{$product->id}/edit")->assertStatus(200);
        }

        if ($category) {
            $this->actingAs($admin)->get("/categories/{$category->id}/edit")->assertStatus(200);
        }

        if ($supplier) {
            $this->actingAs($admin)->get("/suppliers/{$supplier->id}/edit")->assertStatus(200);
        }
    }
}
