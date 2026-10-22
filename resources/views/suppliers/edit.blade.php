@extends('layouts.admin')

@section('title', 'Edit Supplier')
@section('page-title', 'Edit Penerbit: ' . $supplier->name)

@section('content')
<div class="max-w-xl mx-auto">
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 md:p-8">
        <div class="mb-6 pb-4 border-b border-slate-100">
            <h3 class="text-base font-bold text-slate-900">Perbarui Identitas Supplier / Penerbit</h3>
            <p class="text-xs text-slate-500 mt-1">Perubahan kontak akan langsung terupdate pada data master.</p>
        </div>

        <form action="{{ route('suppliers.update', $supplier) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label for="name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Nama Penerbit / Distributor *</label>
                <input type="text" id="name" name="name" value="{{ old('name', $supplier->name) }}" required autofocus
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-red-500 focus:bg-white transition-all">
                @error('name')
                    <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="phone" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Nomor Telepon</label>
                <input type="text" id="phone" name="phone" value="{{ old('phone', $supplier->phone) }}"
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-red-500 focus:bg-white transition-all">
                @error('phone')
                    <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Alamat Email</label>
                <input type="email" id="email" name="email" value="{{ old('email', $supplier->email) }}"
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-red-500 focus:bg-white transition-all">
                @error('email')
                    <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                <a href="{{ route('suppliers.index') }}" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl text-xs font-bold shadow-sm transition-colors">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
