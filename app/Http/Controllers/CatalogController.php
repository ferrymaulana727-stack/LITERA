<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $categoryId = $request->input('category_id');

        $categories = Category::withCount('products')->get();

        $productsQuery = Product::with('category');

        if ($search) {
            $productsQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($categoryId) {
            $productsQuery->where('category_id', $categoryId);
        }

        $products = $productsQuery->latest()->paginate(8)->withQueryString();

        // Ambil keranjang dari session
        $cart = session()->get('cart', []);
        $cartCount = array_sum(array_column($cart, 'quantity'));
        $cartTotal = array_sum(array_map(fn($item) => $item['price'] * $item['quantity'], $cart));

        return view('catalog.index', compact('products', 'categories', 'search', 'categoryId', 'cart', 'cartCount', 'cartTotal'));
    }

    public function addToCart(Request $request): RedirectResponse
    {
        $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        $productId = $request->input('product_id');
        $quantity = (int)($request->input('quantity', 1));

        /** @var Product $product */
        $product = Product::findOrFail($productId);

        if ($product->stock <= 0) {
            return back()->with('error', "Maaf, stok \"{$product->name}\" saat ini sedang habis.");
        }

        $cart = session()->get('cart', []);

        $currentQtyInCart = isset($cart[$productId]) ? $cart[$productId]['quantity'] : 0;
        $newQty = $currentQtyInCart + $quantity;

        if ($newQty > $product->stock) {
            return back()->with('error', "Stok tidak mencukupi. Anda sudah memasukkan {$currentQtyInCart} di keranjang, dan stok tersisa hanya {$product->stock}.");
        }

        $cart[$productId] = [
            'id' => $product->id,
            'sku' => $product->sku,
            'name' => $product->name,
            'image_url' => $product->image_url ?: asset('images/products/default-book.svg'),
            'price' => (float)$product->selling_price,
            'quantity' => $newQty,
            'category_name' => $product->category->name ?? '-',
        ];

        session()->put('cart', $cart);

        return back()->with('success', "\"{$product->name}\" berhasil dimasukkan ke keranjang belanja!");
    }

    public function updateCart(Request $request): RedirectResponse
    {
        $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:0'],
        ]);

        $productId = $request->input('product_id');
        $quantity = (int)$request->input('quantity');

        $cart = session()->get('cart', []);

        if ($quantity <= 0) {
            unset($cart[$productId]);
            session()->put('cart', $cart);
            return back()->with('info', 'Item dihapus dari keranjang belanja.');
        }

        $product = Product::findOrFail($productId);
        if ($quantity > $product->stock) {
            return back()->with('error', "Stok produk \"{$product->name}\" hanya tersedia {$product->stock} pcs.");
        }

        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] = $quantity;
            session()->put('cart', $cart);
        }

        return back()->with('success', 'Keranjang berhasil diperbarui.');
    }

    public function removeFromCart(Request $request, int $productId): RedirectResponse
    {
        $cart = session()->get('cart', []);
        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            session()->put('cart', $cart);
        }

        return back()->with('info', 'Item berhasil dikeluarkan dari keranjang belanja.');
    }

    public function clearCart(): RedirectResponse
    {
        session()->forget('cart');
        return back()->with('info', 'Keranjang belanja telah dikosongkan.');
    }

    public function checkout(Request $request): RedirectResponse
    {
        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('warning', 'Silakan masuk (login) terlebih dahulu untuk melanjutkan pesanan / checkout.');
        }

        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->route('catalog.index')
                ->with('error', 'Keranjang belanja Anda masih kosong. Silakan pilih buku / ATK terlebih dahulu.');
        }

        try {
            $sale = DB::transaction(function () use ($cart) {
                $user = Auth::user();

                // Generate invoice number INV-YYYYMMDD-XXXX
                $datePrefix = Carbon::now()->format('Ymd');
                $countToday = Sale::whereDate('created_at', Carbon::today())->count() + 1;
                $saleNumber = 'INV-' . $datePrefix . '-' . str_pad((string)$countToday, 4, '0', STR_PAD_LEFT);
                while (Sale::where('sale_number', $saleNumber)->exists()) {
                    $countToday++;
                    $saleNumber = 'INV-' . $datePrefix . '-' . str_pad((string)$countToday, 4, '0', STR_PAD_LEFT);
                }

                $calculatedTotal = 0;
                $itemsToInsert = [];

                // Validasi ketat stok di server dengan lockForUpdate
                foreach ($cart as $productId => $cartItem) {
                    $qty = (int)$cartItem['quantity'];

                    /** @var Product $product */
                    $product = Product::lockForUpdate()->find($productId);

                    if (!$product) {
                        throw new Exception("Produk \"{$cartItem['name']}\" tidak ditemukan atau sudah tidak aktif.");
                    }

                    if ($product->stock < $qty) {
                        throw new Exception("Stok untuk \"{$product->name}\" tidak mencukupi (sisa: {$product->stock}, diminta: {$qty}). Seluruh pesanan dibatalkan.");
                    }

                    // Perhitungan harga & total wajib dari database Server
                    $unitPrice = (float)$product->selling_price;
                    $subtotal = $qty * $unitPrice;
                    $calculatedTotal += $subtotal;

                    $itemsToInsert[] = [
                        'product' => $product,
                        'product_id' => $product->id,
                        'quantity' => $qty,
                        'unit_price' => $unitPrice,
                        'subtotal' => $subtotal,
                    ];
                }

                // Buat nota penjualan
                $sale = Sale::create([
                    'user_id' => $user->id,
                    'sale_number' => $saleNumber,
                    'total' => $calculatedTotal,
                    'status' => 'completed',
                    'sold_at' => Carbon::now(),
                ]);

                // Simpan item transaksi dan potong stok otomatis
                foreach ($itemsToInsert as $entry) {
                    $sale->items()->create([
                        'product_id' => $entry['product_id'],
                        'quantity' => $entry['quantity'],
                        'unit_price' => $entry['unit_price'],
                        'subtotal' => $entry['subtotal'],
                    ]);

                    $entry['product']->decrement('stock', $entry['quantity']);
                }

                return $sale;
            });

            // Kosongkan keranjang setelah berhasil
            session()->forget('cart');

            return redirect()->route('orders.show', $sale)
                ->with('success', "Pesanan Anda berhasil dibuat dengan No. Nota {$sale->sale_number}. Terima kasih telah berbelanja di Litera!");
        } catch (Exception $e) {
            return redirect()->route('catalog.index')
                ->with('error', 'Checkout gagal: ' . $e->getMessage());
        }
    }
}
