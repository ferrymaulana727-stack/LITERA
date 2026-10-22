@extends('layouts.admin')

@section('title', 'Dashboard Back-Office')
@section('page-title', 'Ringkasan Sistem Inventori')

@section('content')
<div class="space-y-6">

    <!-- Welcome Header Card -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 rounded-2xl p-6 text-white border border-slate-800 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold tracking-wide uppercase {{ auth()->user()->isAdmin() ? 'bg-red-500/20 text-red-300 border border-red-500/30' : 'bg-blue-500/20 text-blue-300 border border-blue-500/30' }}">
                    {{ auth()->user()->isAdmin() ? 'Administrator / Owner' : 'Staff Toko & Gudang' }}
                </span>
                <span class="text-xs text-slate-400">| Sesi Aktif</span>
            </div>
            <h2 class="text-xl md:text-2xl font-black">Halo, {{ auth()->user()->name }}!</h2>
            <p class="text-xs md:text-sm text-slate-300 mt-1">
                @if(auth()->user()->isAdmin())
                    Pantau kinerja penjualan, stok buku, dan aliran transaksi toko secara komprehensif.
                @else
                    Kelola barang masuk dari penerbit dan layani kasir penjualan dengan cepat.
                @endif
            </p>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('purchases.create') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold bg-white/10 hover:bg-white/20 text-white transition-colors border border-white/10 flex items-center gap-1.5">
                <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>Input Kulakan</span>
            </a>
            <a href="{{ route('sales.create') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold bg-red-600 hover:bg-red-500 text-white transition-colors shadow-sm flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                </svg>
                <span>Buka Kasir</span>
            </a>
        </div>
    </div>

    <!-- Aggregate Stats Cards (Count & Sum) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Produk -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between text-slate-500 text-xs font-semibold uppercase tracking-wider mb-2">
                <span>Total Item Produk</span>
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                </div>
            </div>
            <div class="text-2xl md:text-3xl font-extrabold text-slate-900">{{ number_format($totalProducts) }}</div>
            <p class="text-xs text-slate-500 mt-1">{{ $totalCategories }} Kategori terdaftar</p>
        </div>

        <!-- Stok Rendah Warning Card -->
        <div class="bg-white p-5 rounded-2xl border {{ $lowStockCount > 0 ? 'border-amber-300 bg-amber-50/20' : 'border-slate-200/80' }} shadow-xs">
            <div class="flex items-center justify-between text-slate-500 text-xs font-semibold uppercase tracking-wider mb-2">
                <span>Peringatan Stok Rendah</span>
                <div class="w-8 h-8 rounded-lg {{ $lowStockCount > 0 ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-600' }} flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
            </div>
            <div class="text-2xl md:text-3xl font-extrabold {{ $lowStockCount > 0 ? 'text-amber-600' : 'text-slate-900' }}">
                {{ number_format($lowStockCount) }}
            </div>
            <p class="text-xs {{ $lowStockCount > 0 ? 'text-amber-700 font-medium' : 'text-slate-500' }} mt-1">
                {{ $lowStockCount > 0 ? 'Perlu kulakan ulang segera' : 'Semua stok aman' }}
            </p>
        </div>

        <!-- Total Transaksi Penjualan -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between text-slate-500 text-xs font-semibold uppercase tracking-wider mb-2">
                <span>Total Nota Penjualan</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="text-2xl md:text-3xl font-extrabold text-slate-900">{{ number_format($totalSalesCount) }}</div>
            <p class="text-xs text-slate-500 mt-1">Transaksi sukses terbit</p>
        </div>

        <!-- Omzet (Admin) / Nota Kulakan (Staff) -->
        @if(auth()->user()->isAdmin())
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between text-slate-500 text-xs font-semibold uppercase tracking-wider mb-2">
                <span>Omzet Penjualan</span>
                <div class="w-8 h-8 rounded-lg bg-red-50 text-red-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="text-xl md:text-2xl font-black text-red-700">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
            <p class="text-xs text-slate-500 mt-1">Total pendapatan bersih</p>
        </div>
        @else
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between text-slate-500 text-xs font-semibold uppercase tracking-wider mb-2">
                <span>Nota Kulakan (Stock-In)</span>
                <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                    </svg>
                </div>
            </div>
            <div class="text-2xl md:text-3xl font-extrabold text-slate-900">{{ number_format($totalPurchasesCount) }}</div>
            <p class="text-xs text-slate-500 mt-1">Total pengadaan buku</p>
        </div>
        @endif
    </div>

    <!-- Low-Stock Alert Table (Wajib di Blueprint Modul) -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-pulse"></div>
                <h3 class="font-bold text-slate-900 text-sm">Daftar Produk Stok Rendah (Low-Stock Alert)</h3>
            </div>
            <span class="text-xs font-semibold text-slate-500">Stok &le; Batas Minimum</span>
        </div>

        @if($lowStockProducts->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50/80 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-100">
                        <tr>
                            <th class="px-6 py-3">Kode SKU</th>
                            <th class="px-6 py-3">Judul Buku / Produk</th>
                            <th class="px-6 py-3">Kategori</th>
                            <th class="px-6 py-3 text-center">Sisa Stok</th>
                            <th class="px-6 py-3 text-center">Batas Min.</th>
                            <th class="px-6 py-3 text-right">Harga Jual</th>
                            <th class="px-6 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        @foreach($lowStockProducts as $lowItem)
                            <tr class="hover:bg-amber-50/30 transition-colors">
                                <td class="px-6 py-3 font-mono font-bold text-slate-900">{{ $lowItem->sku }}</td>
                                <td class="px-6 py-3 font-semibold text-slate-900">{{ $lowItem->name }}</td>
                                <td class="px-6 py-3 text-slate-500">{{ $lowItem->category->name ?? '-' }}</td>
                                <td class="px-6 py-3 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-extrabold bg-amber-100 text-amber-800 border border-amber-200">
                                        {{ $lowItem->stock }} pcs
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-center text-slate-500">{{ $lowItem->minimum_stock }} pcs</td>
                                <td class="px-6 py-3 text-right font-semibold text-slate-900">Rp {{ number_format($lowItem->selling_price, 0, ',', '.') }}</td>
                                <td class="px-6 py-3 text-center">
                                    <a href="{{ route('purchases.create') }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-red-50 text-red-700 hover:bg-red-100 transition-colors">
                                        <span>+ Restock</span>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-8 text-center text-slate-400">
                <svg class="w-10 h-10 mx-auto text-emerald-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-sm font-semibold text-slate-700">Semua Persediaan Aman</p>
                <p class="text-xs text-slate-400 mt-0.5">Tidak ada produk yang berada di bawah atau sama dengan batas stok minimum.</p>
            </div>
        @endif
    </div>

    <!-- Recent Sales & Recent Purchases 2-Column Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Transaksi Penjualan Terakhir -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-bold text-slate-900 text-sm">Penjualan Terakhir (Kasir & Web)</h3>
                <a href="{{ route('sales.index') }}" class="text-xs font-bold text-red-600 hover:text-red-700">Lihat Semua &rarr;</a>
            </div>
            @if($recentSales->count() > 0)
                <div class="divide-y divide-slate-100">
                    @foreach($recentSales as $sale)
                        <div class="px-6 py-3.5 flex items-center justify-between hover:bg-slate-50/50 transition-colors">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-xs font-bold text-slate-900">{{ $sale->sale_number }}</span>
                                    <span class="inline-flex px-1.5 py-0.5 text-[10px] font-bold rounded {{ $sale->isCompleted() ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                        {{ $sale->status }}
                                    </span>
                                </div>
                                <div class="text-xs text-slate-500 mt-0.5">
                                    Customer: <strong class="text-slate-700">{{ $sale->user->name ?? 'Guest' }}</strong> &bull; {{ $sale->items->count() }} item
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="font-bold text-xs text-slate-900 block">Rp {{ number_format($sale->total, 0, ',', '.') }}</span>
                                <span class="text-[10px] text-slate-400 block">{{ $sale->sold_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-6 text-center text-xs text-slate-400">Belum ada transaksi penjualan tercatat.</div>
            @endif
        </div>

        <!-- Kulakan / Stock-In Terakhir -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-bold text-slate-900 text-sm">Kulakan Terakhir (Stock-In Penerbit)</h3>
                <a href="{{ route('purchases.index') }}" class="text-xs font-bold text-red-600 hover:text-red-700">Lihat Semua &rarr;</a>
            </div>
            @if($recentPurchases->count() > 0)
                <div class="divide-y divide-slate-100">
                    @foreach($recentPurchases as $purchase)
                        <div class="px-6 py-3.5 flex items-center justify-between hover:bg-slate-50/50 transition-colors">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-xs font-bold text-slate-900">{{ $purchase->purchase_number }}</span>
                                    <span class="inline-flex px-1.5 py-0.5 text-[10px] font-bold rounded {{ $purchase->isCompleted() ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                        {{ $purchase->status }}
                                    </span>
                                </div>
                                <div class="text-xs text-slate-500 mt-0.5">
                                    Penerbit: <strong class="text-slate-700">{{ $purchase->supplier->name ?? '-' }}</strong>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="font-bold text-xs text-slate-900 block">Rp {{ number_format($purchase->total, 0, ',', '.') }}</span>
                                <span class="text-[10px] text-slate-400 block">{{ $purchase->purchased_at->format('d/m/Y') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-6 text-center text-xs text-slate-400">Belum ada nota kulakan barang masuk tercatat.</div>
            @endif
        </div>
    </div>

</div>
@endsection
