<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $categoryId = $request->input('category_id');
        $filterStock = $request->input('stock_status');

        $query = Product::with('category');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($filterStock === 'low') {
            $query->whereColumn('stock', '<=', 'minimum_stock');
        } elseif ($filterStock === 'out') {
            $query->where('stock', '<=', 0);
        }

        $products = $query->latest()->paginate(10)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('products.index', compact('products', 'categories', 'search', 'categoryId', 'filterStock'));
    }

    public function create(Request $request): View
    {
        $this->authorizeAdmin($request);

        $categories = Category::orderBy('name')->get();
        return view('products.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAdmin($request);

        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'sku' => ['required', 'string', 'max:50', 'unique:products,sku'],
            'name' => ['required', 'string', 'max:255'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
        ], [
            'sku.unique' => 'Kode SKU / ISBN ini sudah digunakan.',
            'category_id.required' => 'Pilih kategori buku / ATK.',
            'name.required' => 'Judul buku / nama barang wajib diisi.',
        ]);

        Product::create($validated);

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil ditambahkan ke inventori.');
    }

    public function show(Product $product): View
    {
        $product->load(['category', 'purchaseItems.purchase.supplier', 'saleItems.sale.user']);
        return view('products.show', compact('product'));
    }

    public function edit(Request $request, Product $product): View
    {
        $this->authorizeAdmin($request);

        $categories = Category::orderBy('name')->get();
        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $this->authorizeAdmin($request);

        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'sku' => ['required', 'string', 'max:50', Rule::unique('products', 'sku')->ignore($product->id)],
            'name' => ['required', 'string', 'max:255'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
        ], [
            'sku.unique' => 'Kode SKU / ISBN ini sudah digunakan.',
            'category_id.required' => 'Pilih kategori buku / ATK.',
            'name.required' => 'Judul buku / nama barang wajib diisi.',
        ]);

        $product->update($validated);

        return redirect()->route('products.index')
            ->with('success', 'Data produk berhasil diperbarui.');
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        $this->authorizeAdmin($request);

        // Larangan hard-delete produk yang sudah pernah ditransaksikan (menggunakan SoftDeletes)
        // Eloquent delete() will trigger soft delete because Product uses SoftDeletes trait.
        $hasTransactions = $product->hasTransactions();
        $product->delete();

        $message = $hasTransactions
            ? 'Produk telah di-soft delete dan diarsipkan (riwayat transaksi masa lalu tetap aman).'
            : 'Produk berhasil dihapus.';

        return redirect()->route('products.index')->with('success', $message);
    }

    protected function authorizeAdmin(Request $request): void
    {
        if (!$request->user() || !$request->user()->isAdmin()) {
            abort(403, 'Aksi kelola produk ini hanya dapat dilakukan oleh Admin.');
        }
    }
}
