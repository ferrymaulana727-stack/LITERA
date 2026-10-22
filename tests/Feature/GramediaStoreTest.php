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

class GramediaStoreTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_seeder_populates_required_data(): void
    {
        $this->assertDatabaseHas('users', ['email' => 'admin@gramedia.test', 'role' => 'admin']);
        $this->assertDatabaseHas('users', ['email' => 'staff@gramedia.test', 'role' => 'staff']);
        $this->assertDatabaseHas('users', ['email' => 'customer@gramedia.test', 'role' => 'customer']);

        $this->assertDatabaseCount('categories', 4);
        $this->assertDatabaseHas('categories', ['name' => 'Novel & Sastra']);
        $this->assertDatabaseHas('categories', ['name' => 'Buku Komputer & Pemrograman']);

        $this->assertDatabaseCount('suppliers', 3);
        $this->assertDatabaseHas('suppliers', ['name' => 'PT Gramedia Pustaka Utama']);

        $this->assertDatabaseCount('products', 6);
        $this->assertDatabaseHas('products', ['sku' => 'BKU-NVL-01', 'stock' => 20]);
        $this->assertDatabaseHas('products', ['sku' => 'BKU-TEK-01', 'stock' => 3]);
    }

    public function test_login_redirects_based_on_role(): void
    {
        // Customer login
        $customerResponse = $this->post('/login', [
            'email' => 'customer@gramedia.test',
            'password' => 'password',
        ]);
        $customerResponse->assertRedirect('/catalog');
        $this->assertAuthenticated();

        $this->post('/logout');

        // Admin login
        $adminResponse = $this->post('/login', [
            'email' => 'admin@gramedia.test',
            'password' => 'password',
        ]);
        $adminResponse->assertRedirect('/dashboard');

        $this->post('/logout');

        // Staff login
        $staffResponse = $this->post('/login', [
            'email' => 'staff@gramedia.test',
            'password' => 'password',
        ]);
        $staffResponse->assertRedirect('/dashboard');
    }

    public function test_customer_registration(): void
    {
        $response = $this->post('/register', [
            'name' => 'Ahmad Pembeli',
            'email' => 'ahmad@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertRedirect('/catalog');
        $this->assertDatabaseHas('users', [
            'email' => 'ahmad@example.com',
            'role' => 'customer',
        ]);
    }

    public function test_role_protections_for_customer(): void
    {
        $customer = User::where('role', 'customer')->first();

        // Customer forbidden from dashboard, purchases, users, categories
        $this->actingAs($customer)->get('/dashboard')->assertStatus(403);
        $this->actingAs($customer)->get('/purchases')->assertStatus(403);
        $this->actingAs($customer)->get('/users')->assertStatus(403);
        $this->actingAs($customer)->get('/categories')->assertStatus(403);
        $this->actingAs($customer)->get('/suppliers')->assertStatus(403);

        // Blueprint rule: Products redirects Customer to /catalog
        $this->actingAs($customer)->get('/products')->assertRedirect('/catalog');

        // Blueprint rule: Sales redirects Customer to /my-orders
        $this->actingAs($customer)->get('/sales')->assertRedirect('/my-orders');
    }

    public function test_role_protections_for_staff(): void
    {
        $staff = User::where('role', 'staff')->first();

        // Staff can see dashboard, products, purchases, sales
        $this->actingAs($staff)->get('/dashboard')->assertStatus(200);
        $this->actingAs($staff)->get('/products')->assertStatus(200);
        $this->actingAs($staff)->get('/purchases')->assertStatus(200);
        $this->actingAs($staff)->get('/sales')->assertStatus(200);

        // Staff cannot access user management
        $this->actingAs($staff)->get('/users')->assertStatus(403);

        // Staff cannot create/edit/delete categories or products (Read-Only)
        $this->actingAs($staff)->get('/categories/create')->assertStatus(403);
        $this->actingAs($staff)->post('/categories', ['name' => 'Kategori Baru'])->assertStatus(403);
        $this->actingAs($staff)->get('/products/create/form')->assertStatus(403);
    }

    public function test_purchase_stock_in_and_cancel_restore(): void
    {
        $admin = User::where('role', 'admin')->first();
        $supplier = Supplier::first();
        $product = Product::where('sku', 'BKU-NVL-01')->first();
        $initialStock = $product->stock; // 20

        // Stock-In: Kulakan 10 pcs
        $response = $this->actingAs($admin)->post('/purchases', [
            'supplier_id' => $supplier->id,
            'purchased_at' => now()->format('Y-m-d H:i:s'),
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 10,
                    'unit_cost' => 75000,
                ],
            ],
        ]);

        $response->assertSessionHasNoErrors();
        $product->refresh();
        $this->assertEquals($initialStock + 10, $product->stock); // 30

        $purchase = Purchase::latest()->first();
        $this->assertEquals(750000, $purchase->total);
        $this->assertEquals('completed', $purchase->status);

        // Batalkan Nota Kulakan oleh Admin -> stok berkurang kembali 10
        $cancelResponse = $this->actingAs($admin)->post("/purchases/{$purchase->id}/cancel");
        $cancelResponse->assertSessionHasNoErrors();

        $product->refresh();
        $this->assertEquals($initialStock, $product->stock); // back to 20
        $purchase->refresh();
        $this->assertEquals('cancelled', $purchase->status);
    }

    public function test_sale_stock_out_and_cancel_restore(): void
    {
        $admin = User::where('role', 'admin')->first();
        $product = Product::where('sku', 'BKU-NVL-01')->first();
        $initialStock = $product->stock; // 20

        // Penjualan Kasir: jual 5 pcs
        $response = $this->actingAs($admin)->post('/sales', [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 5,
                ],
            ],
        ]);

        $response->assertSessionHasNoErrors();
        $product->refresh();
        $this->assertEquals($initialStock - 5, $product->stock); // 15

        $sale = Sale::latest()->first();
        $expectedTotal = 5 * (float)$product->selling_price; // 5 * 115000 = 575000
        $this->assertEquals($expectedTotal, (float)$sale->total);
        $this->assertEquals('completed', $sale->status);

        // Batalkan Nota Penjualan oleh Admin -> stok kembali bertambah 5
        $cancelResponse = $this->actingAs($admin)->post("/sales/{$sale->id}/cancel");
        $cancelResponse->assertSessionHasNoErrors();

        $product->refresh();
        $this->assertEquals($initialStock, $product->stock); // back to 20
        $sale->refresh();
        $this->assertEquals('cancelled', $sale->status);
    }

    public function test_sale_transaction_rollback_when_insufficient_stock(): void
    {
        $admin = User::where('role', 'admin')->first();
        $product = Product::where('sku', 'BKU-TEK-01')->first();
        $this->assertEquals(3, $product->stock);

        // Beli 5 pcs padahal stok hanya 3 -> Rollback!
        $response = $this->actingAs($admin)->post('/sales', [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 5,
                ],
            ],
        ]);

        $response->assertSessionHas('error');
        $product->refresh();
        $this->assertEquals(3, $product->stock); // Tidak berkurang sama sekali
        $this->assertDatabaseMissing('sales', ['total' => 5 * (float)$product->selling_price]);
    }

    public function test_customer_cart_and_checkout(): void
    {
        $customer = User::where('role', 'customer')->first();
        $product = Product::where('sku', 'BKU-NVL-02')->first();
        $initialStock = $product->stock; // 12

        // Customer belanja lewat katalog
        $this->actingAs($customer)->post('/cart/add', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        // Checkout
        $response = $this->actingAs($customer)->post('/checkout');
        $response->assertSessionHasNoErrors();

        $product->refresh();
        $this->assertEquals($initialStock - 2, $product->stock); // 10

        $sale = Sale::where('user_id', $customer->id)->latest()->first();
        $this->assertNotNull($sale);
        $this->assertEquals(2 * (float)$product->selling_price, (float)$sale->total);
    }

    public function test_customer_cannot_view_other_customer_orders(): void
    {
        $customer1 = User::where('role', 'customer')->first();
        $customer2 = User::create([
            'name' => 'Customer Lain',
            'email' => 'other@gramedia.test',
            'password' => bcrypt('password'),
            'role' => 'customer',
        ]);

        $sale2 = Sale::create([
            'user_id' => $customer2->id,
            'sale_number' => 'INV-TEST-9999',
            'total' => 100000,
            'status' => 'completed',
            'sold_at' => now(),
        ]);

        // Customer 1 tries to access Customer 2's receipt
        $response = $this->actingAs($customer1)->get("/my-orders/{$sale2->id}");
        $response->assertStatus(403);
    }

    public function test_product_soft_delete(): void
    {
        $admin = User::where('role', 'admin')->first();
        $product = Product::where('sku', 'ATK-KRT-01')->first();

        $response = $this->actingAs($admin)->delete("/products/{$product->id}");
        $response->assertRedirect('/products');

        // Pastikan tidak di-hard delete (masih ada di database dengan deleted_at terisi)
        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }
}
