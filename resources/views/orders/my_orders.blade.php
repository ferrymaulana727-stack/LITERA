@extends('layouts.customer')

@section('title', 'Riwayat Belanja Saya')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">

    <!-- Page Title & Header -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-black text-slate-900">Riwayat Belanja & Nota Anda</h2>
            <p class="text-xs text-slate-500 mt-0.5">Daftar seluruh transaksi pembelian buku dan alat tulis yang pernah Anda lakukan.</p>
        </div>

        <form method="GET" action="{{ route('orders.my') }}" class="flex items-center gap-2">
            <input type="text" name="search" value="{{ $search }}"
                   placeholder="Cari No. Nota (INV)..."
                   class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-1.5 text-xs text-slate-800 focus:outline-none focus:border-red-500">
            <select name="status" class="bg-slate-50 border border-slate-200 rounded-xl px-2.5 py-1.5 text-xs text-slate-800 focus:outline-none focus:border-red-500">
                <option value="">Semua Status</option>
                <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Selesai</option>
                <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
            </select>
            <button type="submit" class="px-3 py-1.5 bg-slate-800 text-white rounded-xl text-xs font-bold hover:bg-slate-700 transition-colors">
                Cari
            </button>
        </form>
    </div>

    <!-- Orders List Cards (Mobile-first friendly) -->
    <div class="space-y-3">
        @forelse($orders as $order)
            <div class="bg-white rounded-2xl border border-slate-200/80 p-4 sm:p-5 shadow-xs hover:border-slate-300 transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="space-y-1.5">
                    <div class="flex items-center gap-2">
                        <span class="font-mono font-black text-slate-900 text-sm sm:text-base">{{ $order->sale_number }}</span>
                        @if($order->isCompleted())
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                Selesai / Terbayar
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                                Dibatalkan
                            </span>
                        @endif
                    </div>

                    <div class="text-xs text-slate-500">
                        <span>Waktu Pembelian: {{ $order->sold_at->format('d M Y, H:i') }} WIB</span>
                        <span class="mx-1">&bull;</span>
                        <span>{{ $order->items->sum('quantity') }} buku/item</span>
                    </div>

                    <!-- Items Preview -->
                    <div class="flex flex-wrap gap-1.5 pt-1">
                        @foreach($order->items->take(3) as $it)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] bg-slate-100 text-slate-700 font-medium">
                                {{ $it->product->name ?? 'Produk' }} ({{ $it->quantity }}x)
                            </span>
                        @endforeach
                        @if($order->items->count() > 3)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] bg-slate-100 text-slate-500 font-medium">
                                +{{ $order->items->count() - 3 }} lainnya
                            </span>
                        @endif
                    </div>
                </div>

                <div class="flex sm:flex-col items-center sm:items-end justify-between sm:justify-center pt-3 sm:pt-0 border-t sm:border-t-0 border-slate-100 shrink-0">
                    <div class="text-left sm:text-right">
                        <span class="text-[10px] text-slate-400 block uppercase font-semibold">Total Tagihan</span>
                        <span class="text-base sm:text-lg font-black text-red-600 font-mono">
                            Rp {{ number_format($order->total, 0, ',', '.') }}
                        </span>
                    </div>

                    <a href="{{ route('orders.show', $order) }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-slate-900 text-white hover:bg-slate-800 text-xs font-bold transition-colors shadow-xs mt-2">
                        <span>Lihat Nota</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>
            </div>
        @empty
            <div class="p-12 text-center text-slate-400 bg-white rounded-2xl border border-slate-200">
                <svg class="w-12 h-12 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <p class="font-bold text-slate-700 text-sm">Belum Ada Transaksi Belanja</p>
                <p class="text-xs text-slate-400 mt-1">Buku dan barang yang Anda beli akan tercatat rapi di halaman ini.</p>
                <a href="{{ route('catalog.index') }}" class="inline-block mt-4 px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-bold rounded-xl shadow-xs transition-colors">
                    Mulai Belanja di Katalog
                </a>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($orders->hasPages())
        <div class="pt-4">
            {{ $orders->links() }}
        </div>
    @endif

</div>
@endsection
