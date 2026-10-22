<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        // Database aggregate queries required by blueprint
        $totalProducts = Product::count();
        $totalCategories = Category::count();
        $totalSuppliers = Supplier::count();

        $lowStockQuery = Product::with('category')->whereColumn('stock', '<=', 'minimum_stock');
        $lowStockCount = (clone $lowStockQuery)->count();
        $lowStockProducts = (clone $lowStockQuery)->orderBy('stock', 'asc')->take(10)->get();

        $totalSalesCount = Sale::where('status', 'completed')->count();
        $totalRevenue = Sale::where('status', 'completed')->sum('total');

        $totalPurchasesCount = Purchase::where('status', 'completed')->count();
        $totalExpense = Purchase::where('status', 'completed')->sum('total');

        $recentSales = Sale::with(['user', 'items.product'])
            ->latest('sold_at')
            ->take(5)
            ->get();

        $recentPurchases = Purchase::with(['supplier', 'user'])
            ->latest('purchased_at')
            ->take(5)
            ->get();

        return view('dashboard.index', compact(
            'user',
            'totalProducts',
            'totalCategories',
            'totalSuppliers',
            'lowStockCount',
            'lowStockProducts',
            'totalSalesCount',
            'totalRevenue',
            'totalPurchasesCount',
            'totalExpense',
            'recentSales',
            'recentPurchases'
        ));
    }
}
