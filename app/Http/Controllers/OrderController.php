<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function myOrders(Request $request): View
    {
        $user = $request->user();
        $search = $request->input('search');
        $status = $request->input('status');

        $query = Sale::with(['items.product.category']);

        // Jika customer, batasi HANYA nota miliknya sendiri
        if ($user->isCustomer()) {
            $query->where('user_id', $user->id);
        }

        if ($search) {
            $query->where('sale_number', 'like', "%{$search}%");
        }

        if ($status && in_array($status, ['completed', 'cancelled'], true)) {
            $query->where('status', $status);
        }

        $orders = $query->latest('sold_at')->paginate(10)->withQueryString();

        return view('orders.my_orders', compact('orders', 'search', 'status'));
    }

    public function showOrder(Request $request, Sale $sale): View
    {
        $user = $request->user();

        // Customer hanya boleh melihat detail nota miliknya sendiri
        if ($user->isCustomer() && $sale->user_id !== $user->id) {
            abort(403, 'Akses ditolak: Anda tidak memiliki izin untuk melihat nota milik customer lain.');
        }

        $sale->load(['user', 'items.product.category']);

        return view('orders.show', compact('sale'));
    }
}
