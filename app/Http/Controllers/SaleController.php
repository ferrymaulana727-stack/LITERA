<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SaleController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = Sale::with(['user', 'items.product']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('sale_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        if ($status && in_array($status, ['completed', 'cancelled'], true)) {
            $query->where('status', $status);
        }

        if ($startDate) {
            $query->whereDate('sold_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('sold_at', '<=', $endDate);
        }

        $sales = $query->latest('sold_at')->paginate(10)->withQueryString();

        return view('sales.index', compact('sales', 'search', 'status', 'startDate', 'endDate'));
    }

    public function create(): View
    {
        $products = Product::where('stock', '>', 0)->orderBy('name')->get();
        $customers = User::where('role', 'customer')->orderBy('name')->get();

        return view('sales.create', compact('products', 'customers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'user_id' => ['nullable', 'exists:users,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ], [
            'items.required' => 'Pilih minimal 1 produk buku atau ATK.',
            'items.*.product_id.required' => 'Pilih produk.',
            'items.*.quantity.min' => 'Jumlah beli minimal 1.',
        ]);

        try {
            $sale = DB::transaction(function () use ($request) {
                $itemsData = $request->input('items', []);

                // Customer default jika tidak dipilih adalah akun kasir/staff yang login
                $buyerId = $request->input('user_id') ?: $request->user()->id;

                // Generate Nomor Nota Unik: INV-YYYYMMDD-XXXX
                $datePrefix = Carbon::now()->format('Ymd');
                $countToday = Sale::whereDate('created_at', Carbon::today())->count() + 1;
                $saleNumber = 'INV-' . $datePrefix . '-' . str_pad((string)$countToday, 4, '0', STR_PAD_LEFT);
                while (Sale::where('sale_number', $saleNumber)->exists()) {
                    $countToday++;
                    $saleNumber = 'INV-' . $datePrefix . '-' . str_pad((string)$countToday, 4, '0', STR_PAD_LEFT);
                }

                $calculatedTotal = 0;
                $validatedItems = [];

                // Pengecekan stok ketat & penguncian baris (lockForUpdate)
                // Wajib di server: jika salah satu buku kurang, seluruh nota rollback!
                foreach ($itemsData as $item) {
                    $productId = $item['product_id'];
                    $qty = (int)$item['quantity'];

                    /** @var Product $product */
                    $product = Product::lockForUpdate()->find($productId);

                    if (!$product) {
                        throw new Exception("Produk dengan ID #{$productId} tidak ditemukan.");
                    }

                    if ($product->stock < $qty) {
                        throw new Exception("Stok untuk \"{$product->name}\" tidak mencukupi! Tersedia: {$product->stock}, Diminta: {$qty}. Seluruh nota dibatalkan.");
                    }

                    // Perhitungan harga murni dari database server, bukan inputan client
                    $unitPrice = (float)$product->selling_price;
                    $subtotal = $qty * $unitPrice;
                    $calculatedTotal += $subtotal;

                    $validatedItems[] = [
                        'product' => $product,
                        'product_id' => $product->id,
                        'quantity' => $qty,
                        'unit_price' => $unitPrice,
                        'subtotal' => $subtotal,
                    ];
                }

                // Buat Header Nota Penjualan
                $sale = Sale::create([
                    'user_id' => $buyerId,
                    'sale_number' => $saleNumber,
                    'total' => $calculatedTotal,
                    'status' => 'completed',
                    'sold_at' => Carbon::now(),
                ]);

                // Simpan detail item dan kurangi stok
                foreach ($validatedItems as $entry) {
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

            return redirect()->route('sales.show', $sale)
                ->with('success', "Transaksi kasir nota {$sale->sale_number} berhasil diproses. Stok buku telah terpotong.");
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Gagal memproses transaksi: ' . $e->getMessage());
        }
    }

    public function show(Sale $sale): View
    {
        $sale->load(['user', 'items.product.category']);
        return view('sales.show', compact('sale'));
    }

    public function cancel(Request $request, Sale $sale): RedirectResponse
    {
        // Hanya Admin yang berhak membatalkan nota penjualan
        if (!$request->user() || !$request->user()->isAdmin()) {
            abort(403, 'Hanya Admin yang memiliki hak untuk membatalkan nota penjualan.');
        }

        if ($sale->isCancelled()) {
            return back()->with('error', 'Nota penjualan ini sudah berstatus dibatalkan sebelumnya.');
        }

        try {
            DB::transaction(function () use ($sale) {
                // Pulihkan kembali stok produk ke etalase toko
                foreach ($sale->items as $item) {
                    /** @var Product $product */
                    $product = Product::lockForUpdate()->find($item->product_id);
                    if ($product) {
                        $product->increment('stock', $item->quantity);
                    }
                }

                $sale->update(['status' => 'cancelled']);
            });

            return redirect()->route('sales.show', $sale)
                ->with('success', "Nota penjualan {$sale->sale_number} berhasil dibatalkan. Seluruh stok produk telah dikembalikan ke etalase.");
        } catch (Exception $e) {
            return back()->with('error', 'Gagal membatalkan nota: ' . $e->getMessage());
        }
    }
}
