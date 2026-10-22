@extends('layouts.admin')

@section('title', 'Tambah Produk Baru')
@section('page-title', 'Tambah Buku / Alat Tulis Baru')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 md:p-8">
        <div class="mb-6 pb-4 border-b border-slate-100">
            <h3 class="text-base font-bold text-slate-900">Form Master Produk Baru</h3>
            <p class="text-xs text-slate-500 mt-1">Lengkapi data buku atau perlengkapan kantor untuk didaftarkan ke sistem inventori.</p>
        </div>

        <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="sku" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Kode SKU / ISBN *</label>
                    <input type="text" id="sku" name="sku" value="{{ old('sku') }}" required autofocus
                           placeholder="Contoh: BKU-NVL-03"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-red-500 focus:bg-white transition-all">
                    @error('sku')
                        <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="category_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Kategori Produk *</label>
                    <select id="category_id" name="category_id" required
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-red-500 focus:bg-white transition-all">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Judul Buku / Nama Barang *</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required
                       placeholder="Contoh: Cantik Itu Luka - Eka Kurniawan"
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-red-500 focus:bg-white transition-all">
                @error('name')
                    <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="image" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Foto / Cover Buku (Opsional)</label>
                <input type="file" id="image" name="image" accept="image/*"
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2 text-xs text-slate-600 file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100 transition-all">
                <p class="text-[11px] text-slate-400 mt-1">Format: JPG, PNG, WEBP, atau SVG (Maks. 2MB). Jika dikosongkan, cover standar akan digunakan.</p>
                @error('image')
                    <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="purchase_price" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Harga Beli / Modal (Rp) *</label>
                    <input type="number" id="purchase_price" name="purchase_price" value="{{ old('purchase_price') }}" required min="0" step="1000"
                           placeholder="75000"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-red-500 focus:bg-white transition-all">
                    @error('purchase_price')
                        <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="selling_price" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Harga Jual (Rp) *</label>
                    <input type="number" id="selling_price" name="selling_price" value="{{ old('selling_price') }}" required min="0" step="1000"
                           placeholder="115000"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-red-500 focus:bg-white transition-all">
                    @error('selling_price')
                        <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="stock" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Stok Awal Fisik *</label>
                    <input type="number" id="stock" name="stock" value="{{ old('stock', 0) }}" required min="0"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-red-500 focus:bg-white transition-all">
                    @error('stock')
                        <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="minimum_stock" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Batas Minimum Stok (Alert) *</label>
                    <input type="number" id="minimum_stock" name="minimum_stock" value="{{ old('minimum_stock', 5) }}" required min="0"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-red-500 focus:bg-white transition-all">
                    @error('minimum_stock')
                        <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                <a href="{{ route('products.index') }}" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl text-xs font-bold shadow-sm transition-colors">
                    Simpan Produk
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
