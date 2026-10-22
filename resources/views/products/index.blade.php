@extends('layouts.admin')

@section('title', 'Katalog Produk')
@section('page-title', 'Master Data Produk (Buku & ATK)')

@section('content')
<div class="space-y-6">

    <!-- Filter & Action Bar -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <!-- Search & Filters -->
            <form method="GET" action="{{ route('products.index') }}" class="flex-1 grid grid-cols-1 sm:grid-cols-3 gap-3">
                <!-- Search input -->
                <div class="relative">
                    <input type="text" name="search" value="{{ $search }}"
                           placeholder="Cari SKU atau judul buku..."
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-9 pr-3 py-2 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-red-500 focus:bg-white transition-all">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>

                <!-- Category filter -->
                <div>
                    <select name="category_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-red-500 focus:bg-white transition-all">
                        <option value="">-- Semua Kategori --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Stock Status filter -->
                <div class="flex items-center gap-2">
                    <select name="stock_status" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-red-500 focus:bg-white transition-all">
                        <option value="">-- Status Stok (Semua) --</option>
                        <option value="low" {{ $filterStock === 'low' ? 'selected' : '' }}>⚠️ Stok Rendah (&le; Min)</option>
                        <option value="out" {{ $filterStock === 'out' ? 'selected' : '' }}>❌ Stok Habis (0)</option>
                    </select>
                    <button type="submit" class="px-4 py-2 bg-slate-800 text-white rounded-xl text-xs font-bold hover:bg-slate-700 transition-colors shrink-0">
                        Filter
                    </button>
                    @if($search || $categoryId || $filterStock)
                        <a href="{{ route('products.index') }}" class="p-2 text-slate-400 hover:text-slate-600 rounded-lg shrink-0" title="Reset Filter">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </a>
                    @endif
                </div>
            </form>

            @if(auth()->user()->isAdmin())
                <a href="{{ route('products.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-bold rounded-xl shadow-sm transition-colors shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Tambah Produk Baru</span>
                </a>
            @else
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 text-slate-500 text-xs font-semibold shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    <span>Mode Lihat (Staff Read-Only)</span>
                </span>
            @endif
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50/80 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-100">
                    <tr>
                        <th class="px-5 py-3.5 w-14 text-center">No</th>
                        <th class="px-5 py-3.5">Kode SKU / ISBN</th>
                        <th class="px-5 py-3.5">Judul Buku / Nama Barang</th>
                        <th class="px-5 py-3.5">Kategori</th>
                        <th class="px-5 py-3.5 text-right">Harga Beli</th>
                        <th class="px-5 py-3.5 text-right">Harga Jual</th>
                        <th class="px-5 py-3.5 text-center">Stok Fisik</th>
                        <th class="px-5 py-3.5 text-center">Status</th>
                        <th class="px-5 py-3.5 text-center w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse($products as $index => $prod)
                        <tr class="hover:bg-slate-50/50 transition-colors {{ $prod->isLowStock() ? 'bg-amber-50/20' : '' }}">
                            <td class="px-5 py-4 text-center text-slate-400 font-mono">{{ $products->firstItem() + $index }}</td>
                            <td class="px-5 py-4 font-mono font-bold text-slate-900">{{ $prod->sku }}</td>
                            <td class="px-5 py-4">
                                <a href="{{ route('products.show', $prod) }}" class="font-bold text-slate-900 hover:text-red-600 transition-colors text-sm block">
                                    {{ $prod->name }}
                                </a>
                            </td>
                            <td class="px-5 py-4 text-slate-500">{{ $prod->category->name ?? '-' }}</td>
                            <td class="px-5 py-4 text-right font-mono text-slate-500">Rp {{ number_format($prod->purchase_price, 0, ',', '.') }}</td>
                            <td class="px-5 py-4 text-right font-mono font-bold text-slate-900">Rp {{ number_format($prod->selling_price, 0, ',', '.') }}</td>
                            <td class="px-5 py-4 text-center">
                                <span class="font-bold font-mono text-sm {{ $prod->stock <= 0 ? 'text-rose-600' : ($prod->isLowStock() ? 'text-amber-600' : 'text-slate-900') }}">
                                    {{ $prod->stock }}
                                </span>
                                <span class="text-[10px] text-slate-400 block font-normal">(Min: {{ $prod->minimum_stock }})</span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                @if($prod->stock <= 0)
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                                        Habis
                                    </span>
                                @elseif($prod->isLowStock())
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                        ⚠️ Low Stock
                                    </span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        Aman
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-center">
                                <div class="inline-flex items-center gap-1">
                                    <a href="{{ route('products.show', $prod) }}" class="p-1.5 text-slate-500 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition-colors" title="Lihat Detail">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>

                                    @if(auth()->user()->isAdmin())
                                        <a href="{{ route('products.edit', $prod) }}" class="p-1.5 text-slate-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Edit Produk">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </a>

                                        <form action="{{ route('products.destroy', $prod) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk ini? Produk yang memiliki transaksi akan di-soft delete secara aman.')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-slate-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus (Soft Delete)">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center text-slate-400">
                                Tidak ada produk yang sesuai dengan kriteria pencarian atau filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $products->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
