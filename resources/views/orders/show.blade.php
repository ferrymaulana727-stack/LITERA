@extends('layouts.customer')

@section('title', 'Nota Belanja: ' . $sale->sale_number)

@section('content')
<div class="space-y-6 max-w-3xl mx-auto">

    <!-- Top Navigation -->
    <div class="flex items-center justify-between">
        <a href="{{ route('orders.my') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-slate-800 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            <span>Kembali ke Riwayat Belanja</span>
        </a>

        <button onclick="window.print()" class="px-3 py-1.5 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold rounded-xl transition-colors flex items-center gap-1.5 shadow-xs">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
            </svg>
            <span>Cetak Struk Nota</span>
        </button>
    </div>

    <!-- Official Customer Receipt -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 sm:p-8">
        <!-- Receipt Header -->
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 pb-6 border-b border-slate-100">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-xs font-extrabold uppercase tracking-widest text-red-600">LITERA STORE</span>
                    @if($sale->isCompleted())
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                            Lunas / Selesai
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                            Dibatalkan
                        </span>
                    @endif
                </div>
                <h2 class="text-xl sm:text-2xl font-black font-mono text-slate-900">{{ $sale->sale_number }}</h2>
                <p class="text-xs text-slate-400 mt-1">Waktu Pembelian: {{ $sale->sold_at->format('d F Y, H:i') }} WIB</p>
            </div>

            <div class="sm:text-right bg-slate-50 p-4 rounded-xl border border-slate-100">
                <span class="text-[10px] text-slate-400 block uppercase font-bold">Total Pembayaran</span>
                <span class="text-xl sm:text-2xl font-black text-red-600 font-mono block mt-0.5">
                    Rp {{ number_format($sale->total, 0, ',', '.') }}
                </span>
            </div>
        </div>

        <!-- Customer Identity Info -->
        <div class="py-5 border-b border-slate-100 text-xs grid grid-cols-2 gap-4">
            <div>
                <span class="font-bold text-slate-400 uppercase tracking-wider block mb-1">Nama Customer</span>
                <p class="font-bold text-slate-900 text-sm">{{ $sale->user->name ?? 'Customer' }}</p>
                <p class="text-slate-500">{{ $sale->user->email ?? '-' }}</p>
            </div>
            <div class="text-right">
                <span class="font-bold text-slate-400 uppercase tracking-wider block mb-1">Metode Transaksi</span>
                <p class="font-semibold text-slate-800">Litera Web Order / Kasir</p>
                <p class="text-emerald-600 font-bold">Verifikasi Server Sukses</p>
            </div>
        </div>

        <!-- Items Table -->
        <div class="pt-5">
            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-3">Rincian Buku yang Dibeli</h4>
            <div class="overflow-x-auto rounded-xl border border-slate-200">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-3 w-10 text-center">No</th>
                            <th class="px-4 py-3">Judul Buku / Produk</th>
                            <th class="px-4 py-3 text-center">Jumlah</th>
                            <th class="px-4 py-3 text-right">Harga Satuan</th>
                            <th class="px-4 py-3 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        @foreach($sale->items as $idx => $item)
                            <tr>
                                <td class="px-4 py-3 text-center text-slate-400">{{ $idx + 1 }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $item->product ? ($item->product->image_url ?: asset('images/products/default-book.svg')) : asset('images/products/default-book.svg') }}" 
                                             alt="{{ $item->product->name ?? 'Buku' }}" 
                                             class="w-8 h-11 object-cover rounded shadow-xs border border-slate-200 shrink-0">
                                        <div>
                                            <div class="font-bold text-slate-900">{{ $item->product->name ?? 'Produk Dihapus' }}</div>
                                            <span class="text-[10px] font-mono text-slate-400">{{ $item->product->sku ?? '-' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center font-bold font-mono">{{ $item->quantity }} pcs</td>
                                <td class="px-4 py-3 text-right font-mono text-slate-600">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right font-mono font-bold text-slate-900">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-slate-50 font-bold border-t border-slate-200">
                        <tr>
                            <td colspan="4" class="px-4 py-3 text-right uppercase text-slate-600">Total Tagihan :</td>
                            <td class="px-4 py-3 text-right font-mono text-sm text-red-600">
                                Rp {{ number_format($sale->total, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="mt-8 pt-4 border-t border-slate-100 text-center text-xs text-slate-400">
            Terima kasih telah berbelanja di Litera — Toko Buku & Alat Tulis.<br>
            Buku dan barang pesanan Anda siap untuk diambil di counter atau dikirimkan sesuai instruksi toko.
        </div>
    </div>

</div>
@endsection
