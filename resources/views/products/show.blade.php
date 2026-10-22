@extends('layouts.admin')

@section('title', 'Detail Produk: ' . $product->name)
@section('page-title', 'Detail Produk')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">

    <!-- Back button & Header -->
    <div class="flex items-center justify-between">
        <a href="{{ route('products.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-slate-800 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            <span>Kembali ke Master Produk</span>
        </a>

        @if(auth()->user()->isAdmin())
            <div class="flex items-center gap-2">
                <a href="{{ route('products.edit', $product) }}" class="px-3.5 py-1.5 rounded-xl bg-blue-50 text-blue-700 hover:bg-blue-100 text-xs font-bold transition-colors">
                    Edit Produk
                </a>
            </div>
        @endif
    </div>

    <!-- Product Details Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 md:p-8">
        <div class="flex flex-col md:flex-row md:items-start justify-between gap-6 pb-6 border-b border-slate-100">
            <div class="flex items-start gap-5">
                <img src="{{ $product->image_url ?: asset('images/products/default-book.svg') }}" 
                     alt="{{ $product->name }}" 
                     class="w-24 sm:w-28 h-32 sm:h-36 object-cover rounded-xl shadow-md border border-slate-200 shrink-0">
                <div>
                    <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700 mb-2">
                        {{ $product->category->name ?? 'Tanpa Kategori' }}
                    </span>
                    <h2 class="text-xl md:text-2xl font-black text-slate-900">{{ $product->name }}</h2>
                    <p class="text-xs font-mono text-slate-400 mt-1">SKU / ISBN: <strong class="text-slate-700">{{ $product->sku }}</strong></p>
                </div>
            </div>

            <div class="flex md:flex-col items-end gap-2 shrink-0">
                <div class="text-right">
                    <span class="text-xs text-slate-400 block">Harga Jual Konsumen</span>
                    <span class="text-2xl font-black text-red-600">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</span>
                </div>
                <div class="text-right text-xs text-slate-400">
                    Harga Modal: <strong class="text-slate-600">Rp {{ number_format($product->purchase_price, 0, ',', '.') }}</strong>
                </div>
            </div>
        </div>

        <!-- Inventory Stats -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 py-6 border-b border-slate-100">
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">Stok Fisik Tersedia</span>
                <span class="text-2xl font-black {{ $product->isLowStock() ? 'text-amber-600' : 'text-slate-900' }}">
                    {{ $product->stock }} <span class="text-xs font-normal text-slate-500">pcs</span>
                </span>
            </div>
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">Batas Stok Minimum</span>
                <span class="text-2xl font-black text-slate-700">
                    {{ $product->minimum_stock }} <span class="text-xs font-normal text-slate-500">pcs</span>
                </span>
            </div>
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">Status Inventori</span>
                <div class="mt-1">
                    @if($product->stock <= 0)
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800">
                            Stok Habis
                        </span>
                    @elseif($product->isLowStock())
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                            ⚠️ Stok Rendah
                        </span>
                    @else
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                            Stok Aman
                        </span>
                    @endif
                </div>
            </div>
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">Riwayat Transaksi</span>
                <span class="text-sm font-bold text-slate-700 mt-1 block">
                    {{ $product->purchaseItems->count() }} Masuk &bull; {{ $product->saleItems->count() }} Terjual
                </span>
            </div>
        </div>

        <!-- Recent Sales of This Product -->
        <div class="pt-6">
            <h4 class="text-sm font-bold text-slate-900 mb-3">Histori Penjualan Terakhir</h4>
            @if($product->saleItems->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100">
                            <tr>
                                <th class="px-4 py-2">No. Nota</th>
                                <th class="px-4 py-2">Customer / Kasir</th>
                                <th class="px-4 py-2 text-center">Jumlah</th>
                                <th class="px-4 py-2 text-right">Subtotal</th>
                                <th class="px-4 py-2">Waktu Transaksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @foreach($product->saleItems->take(5) as $si)
                                <tr>
                                    <td class="px-4 py-2.5 font-mono font-bold text-slate-900">{{ $si->sale->sale_number ?? '-' }}</td>
                                    <td class="px-4 py-2.5">{{ $si->sale->user->name ?? '-' }}</td>
                                    <td class="px-4 py-2.5 text-center font-bold">{{ $si->quantity }} pcs</td>
                                    <td class="px-4 py-2.5 text-right font-semibold">Rp {{ number_format($si->subtotal, 0, ',', '.') }}</td>
                                    <td class="px-4 py-2.5 text-slate-400">{{ $si->sale ? $si->sale->sold_at->format('d/m/Y H:i') : '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-xs text-slate-400">Belum ada riwayat penjualan untuk produk ini.</p>
            @endif
        </div>
    </div>

</div>
@endsection
