<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = Purchase::with(['supplier', 'user', 'items.product']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('purchase_number', 'like', "%{$search}%")
                  ->orWhereHas('supplier', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($status && in_array($status, ['completed', 'cancelled'], true)) {
            $query->where('status', $status);
        }

        if ($startDate) {
            $query->whereDate('purchased_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('purchased_at', '<=', $endDate);
        }

        $purchases = $query->latest('purchased_at')->paginate(10)->withQueryString();

        return view('purchases.index', compact('purchases', 'search', 'status', 'startDate', 'endDate'));
    }

    public function create(): View
    {
        $suppliers = Supplier::orderBy('name')->get();
        $products = Product::orderBy('name')->get();

        return view('purchases.create', compact('suppliers', 'products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'purchased_at' => ['required', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ], [
            'supplier_id.required' => 'Pilih supplier / penerbit sumber kulakan.',
            'items.required' => 'Minimal masukkan 1 item buku / ATK.',
            'items.*.product_id.required' => 'Pilih produk.',
            'items.*.quantity.min' => 'Jumlah barang masuk minimal 1.',
            'items.*.unit_cost.min' => 'Harga modal/beli tidak boleh negatif.',
        ]);

        try {
            $purchase = DB::transaction(function () use ($request) {
                $itemsData = $request->input('items', []);

                // Generate unique purchase number PO-YYYYMMDD-XXXX
                $datePrefix = Carbon::parse($request->input('purchased_at'))->format('Ymd');
                $countToday = Purchase::whereDate('created_at', Carbon::today())->count() + 1;
                $purchaseNumber = 'PO-' . $datePrefix . '-' . str_pad((string)$countToday, 4, '0', STR_PAD_LEFT);
                while (Purchase::where('purchase_number', $purchaseNumber)->exists()) {
                    $countToday++;
                    $purchaseNumber = 'PO-' . $datePrefix . '-' . str_pad((string)$countToday, 4, '0', STR_PAD_LEFT);
                }

                // Perhitungan total di Server
                $calculatedTotal = 0;
                $processedItems = [];

                foreach ($itemsData as $item) {
                    $productId = $item['product_id'];
                    $qty = (int)$item['quantity'];
                    $unitCost = (float)$item['unit_cost'];
                    $subtotal = $qty * $unitCost;
                    $calculatedTotal += $subtotal;

                    $processedItems[] = [
                        'product_id' => $productId,
                        'quantity' => $qty,
                        'unit_cost' => $unitCost,
                        'subtotal' => $subtotal,
                    ];
                }

                // Buat nota purchase
                $purchase = Purchase::create([
                    'supplier_id' => $request->input('supplier_id'),
                    'user_id' => $request->user()->id,
                    'purchase_number' => $purchaseNumber,
                    'total' => $calculatedTotal,
                    'status' => 'completed',
                    'purchased_at' => $request->input('purchased_at'),
                ]);

                // Simpan item & update stok otomatis dengan lockForUpdate
                foreach ($processedItems as $item) {
                    $purchase->items()->create($item);

                    /** @var Product $product */
                    $product = Product::lockForUpdate()->find($item['product_id']);
                    if ($product) {
                        $product->increment('stock', $item['quantity']);
                        // Update purchase_price jika ada nilai terbaru
                        $product->update(['purchase_price' => $item['unit_cost']]);
                    }
                }

                return $purchase;
            });

            return redirect()->route('purchases.show', $purchase)
                ->with('success', "Nota kulakan {$purchase->purchase_number} berhasil diproses dan stok telah ditambahkan otomatis.");
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Gagal memproses stock-in: ' . $e->getMessage());
        }
    }

    public function show(Purchase $purchase): View
    {
        $purchase->load(['supplier', 'user', 'items.product']);
        return view('purchases.show', compact('purchase'));
    }

    public function cancel(Request $request, Purchase $purchase): RedirectResponse
    {
        // Hanya Admin yang berhak membatalkan nota pembelian
        if (!$request->user() || !$request->user()->isAdmin()) {
            abort(403, 'Hanya Admin yang berhak membatalkan nota transaksi.');
        }

        if ($purchase->isCancelled()) {
            return back()->with('error', 'Nota ini sudah dalam status dibatalkan sebelumnya.');
        }

        try {
            DB::transaction(function () use ($purchase) {
                // Pulihkan / kurangi stok yang sebelumnya bertambah karena kulakan
                foreach ($purchase->items as $item) {
                    /** @var Product $product */
                    $product = Product::lockForUpdate()->find($item->product_id);
                    if ($product) {
                        $product->decrement('stock', $item->quantity);
                    }
                }

                $purchase->update(['status' => 'cancelled']);
            });

            return redirect()->route('purchases.show', $purchase)
                ->with('success', "Nota {$purchase->purchase_number} berhasil dibatalkan. Stok barang telah ditarik kembali secara otomatis.");
        } catch (Exception $e) {
            return back()->with('error', 'Gagal membatalkan nota pembelian: ' . $e->getMessage());
        }
    }
}
