@extends('layouts.admin')

@section('title', 'Nota Penjualan: ' . $sale->sale_number)
@section('page-title', 'Detail Nota Penjualan')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">

    <!-- Top Action Nav -->
    <div class="flex items-center justify-between">
        <a href="{{ route('sales.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-slate-800 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            <span>Kembali ke Riwayat Penjualan</span>
        </a>

        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-colors flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                <span>Cetak Struk Nota</span>
            </button>

            @if(auth()->user()->isAdmin() && $sale->isCompleted())
                <form action="{{ route('sales.cancel', $sale) }}" method="POST"
                      onsubmit="return confirm('PERHATIAN: Apakah Anda yakin ingin MEMBATALKAN nota penjualan ini? Stok buku akan otomatis DIKEMBALIKAN ke inventori etalase.')">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold rounded-xl border border-rose-200 transition-colors flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>Batalkan Nota Ini (Admin)</span>
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Printable Receipt Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 md:p-8" id="receipt">
        <!-- Receipt Brand -->
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 pb-6 border-b border-slate-100">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-xs font-extrabold uppercase tracking-widest text-slate-400">Nota Transaksi Penjualan</span>
                    @if($sale->isCompleted())
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                            Completed / Terbayar
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200">
                            Cancelled / Stok Dikembalikan
                        </span>
                    @endif
                </div>
                <h2 class="text-2xl font-black font-mono text-slate-900">{{ $sale->sale_number }}</h2>
                <p class="text-xs text-slate-500 mt-1">Waktu: {{ $sale->sold_at->format('d F Y, H:i') }} WIB</p>
            </div>

            <div class="sm:text-right bg-slate-50 p-4 rounded-xl border border-slate-100 sm:min-w-[220px]">
                <span class="text-xs text-slate-400 block font-semibold uppercase">Total Pembayaran</span>
                <span class="text-2xl font-black text-red-600 block mt-0.5 font-mono">
                    Rp {{ number_format($sale->total, 0, ',', '.') }}
                </span>
            </div>
        </div>

        <!-- Customer Meta -->
        <div class="py-6 border-b border-slate-100 text-xs grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <span class="font-bold text-slate-400 uppercase tracking-wider block mb-1">Data Pembeli (Customer)</span>
                <p class="font-bold text-slate-900 text-sm">{{ $sale->user->name ?? 'Pembeli Kasir Umum' }}</p>
                <p class="text-slate-500 mt-0.5">{{ $sale->user->email ?? '-' }}</p>
            </div>
            <div class="sm:text-right">
                <span class="font-bold text-slate-400 uppercase tracking-wider block mb-1">Metode & Status Kasir</span>
                <p class="font-semibold text-slate-800">Pembayaran Kasir Langsung</p>
                <p class="text-emerald-600 font-bold">Lunas (Server Verified)</p>
            </div>
        </div>

        <!-- Items Table -->
        <div class="pt-6">
            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-3">Rincian Buku & ATK</h4>
            <div class="overflow-x-auto rounded-xl border border-slate-200">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-3 w-12 text-center">No</th>
                            <th class="px-4 py-3">Kode SKU</th>
                            <th class="px-4 py-3">Judul Buku / Nama Produk</th>
                            <th class="px-4 py-3 text-center">Jumlah Beli</th>
                            <th class="px-4 py-3 text-right">Harga Satuan</th>
                            <th class="px-4 py-3 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        @foreach($sale->items as $idx => $item)
                            <tr>
                                <td class="px-4 py-3 text-center text-slate-400">{{ $idx + 1 }}</td>
                                <td class="px-4 py-3 font-mono font-bold text-slate-900">{{ $item->product->sku ?? '-' }}</td>
                                <td class="px-4 py-3 font-semibold text-slate-900">{{ $item->product->name ?? 'Produk Dihapus' }}</td>
                                <td class="px-4 py-3 text-center font-bold font-mono">{{ $item->quantity }} pcs</td>
                                <td class="px-4 py-3 text-right font-mono text-slate-600">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right font-mono font-bold text-slate-900">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-slate-50 font-bold border-t border-slate-200">
                        <tr>
                            <td colspan="5" class="px-4 py-3.5 text-right uppercase text-slate-600">Total Belanja :</td>
                            <td class="px-4 py-3.5 text-right font-mono text-sm text-red-600">
                                Rp {{ number_format($sale->total, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="mt-8 pt-4 border-t border-slate-100 text-center text-[11px] text-slate-400">
            Terima kasih telah berbelanja di Litera — Toko Buku & Alat Tulis. Simpan nota ini sebagai bukti transaksi resmi.
        </div>
    </div>

</div>
@endsection
