@extends('layouts.admin')

@section('title', 'Stock-In / Kulakan')
@section('page-title', 'Pengadaan Barang Masuk (Stock-In)')

@section('content')
<div class="space-y-6">

    <!-- Filters & Action Bar -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <!-- Search & Filters -->
            <form method="GET" action="{{ route('purchases.index') }}" class="flex-1 grid grid-cols-1 sm:grid-cols-4 gap-3">
                <!-- Search input -->
                <div class="relative">
                    <input type="text" name="search" value="{{ $search }}"
                           placeholder="No. Nota PO atau nama penerbit..."
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-9 pr-3 py-2 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-red-500 focus:bg-white transition-all">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>

                <!-- Status Filter -->
                <div>
                    <select name="status" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-red-500 focus:bg-white transition-all">
                        <option value="">-- Status Nota (Semua) --</option>
                        <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>✅ Selesai (Completed)</option>
                        <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>❌ Dibatalkan (Cancelled)</option>
                    </select>
                </div>

                <!-- Date Range -->
                <div class="flex items-center gap-1.5">
                    <input type="date" name="start_date" value="{{ $startDate }}"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-2.5 py-2 text-xs text-slate-800 focus:outline-none focus:border-red-500 focus:bg-white transition-all">
                    <span class="text-xs text-slate-400">-</span>
                    <input type="date" name="end_date" value="{{ $endDate }}"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-2.5 py-2 text-xs text-slate-800 focus:outline-none focus:border-red-500 focus:bg-white transition-all">
                </div>

                <!-- Submit / Reset -->
                <div class="flex items-center gap-2">
                    <button type="submit" class="px-4 py-2 bg-slate-800 text-white rounded-xl text-xs font-bold hover:bg-slate-700 transition-colors">
                        Filter
                    </button>
                    @if($search || $status || $startDate || $endDate)
                        <a href="{{ route('purchases.index') }}" class="p-2 text-slate-400 hover:text-slate-600 rounded-lg shrink-0" title="Reset Filter">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </a>
                    @endif
                </div>
            </form>

            <a href="{{ route('purchases.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-bold rounded-xl shadow-sm transition-colors shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>Input Barang Masuk (Kulakan)</span>
            </a>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50/80 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-3.5 w-16 text-center">No</th>
                        <th class="px-6 py-3.5">No. Nota PO</th>
                        <th class="px-6 py-3.5">Penerbit / Supplier</th>
                        <th class="px-6 py-3.5">Petugas Input</th>
                        <th class="px-6 py-3.5 text-center">Jml Item</th>
                        <th class="px-6 py-3.5 text-right">Total Kulakan</th>
                        <th class="px-6 py-3.5 text-center">Status</th>
                        <th class="px-6 py-3.5">Tanggal Nota</th>
                        <th class="px-6 py-3.5 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse($purchases as $index => $item)
                        <tr class="hover:bg-slate-50/50 transition-colors {{ $item->isCancelled() ? 'opacity-60 bg-slate-50/50' : '' }}">
                            <td class="px-6 py-4 text-center text-slate-400 font-mono">{{ $purchases->firstItem() + $index }}</td>
                            <td class="px-6 py-4 font-mono font-bold text-slate-900 text-sm">
                                <a href="{{ route('purchases.show', $item) }}" class="hover:text-red-600 transition-colors">
                                    {{ $item->purchase_number }}
                                </a>
                            </td>
                            <td class="px-6 py-4 font-semibold text-slate-900">{{ $item->supplier->name ?? '-' }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $item->user->name ?? '-' }}</td>
                            <td class="px-6 py-4 text-center font-bold">{{ $item->items->sum('quantity') }} pcs</td>
                            <td class="px-6 py-4 text-right font-mono font-extrabold text-slate-900 text-sm">
                                Rp {{ number_format($item->total, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($item->isCompleted())
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        Completed
                                    </span>
                                @else
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                        Cancelled
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-slate-500">{{ $item->purchased_at->format('d/m/Y H:i') }}</td>
                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('purchases.show', $item) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center text-slate-400">
                                Belum ada riwayat transaksi nota kulakan penerbit.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($purchases->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $purchases->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
