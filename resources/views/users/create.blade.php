@extends('layouts.admin')

@section('title', 'Tambah User Baru')
@section('page-title', 'Tambah Akun Pengguna')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 md:p-8">
        <div class="mb-6 pb-4 border-b border-slate-100">
            <h3 class="text-base font-bold text-slate-900">Form Pembuatan Akun Baru</h3>
            <p class="text-xs text-slate-500 mt-1">Tentukan role hak akses akun: Administrator, Staff, atau Customer.</p>
        </div>

        <form action="{{ route('users.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Nama Lengkap *</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus
                       placeholder="Contoh: Budi Santoso"
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-red-500 focus:bg-white transition-all">
                @error('name')
                    <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Alamat Email *</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required
                       placeholder="nama@gramedia.test"
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-red-500 focus:bg-white transition-all">
                @error('email')
                    <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="role" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Role / Hak Akses *</label>
                <select id="role" name="role" required
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-red-500 focus:bg-white transition-all">
                    <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin (Full Back-Office & Batalkan Nota)</option>
                    <option value="staff" {{ old('role') === 'staff' ? 'selected' : '' }}>Staff (Gudang & Kasir)</option>
                    <option value="customer" {{ old('role') === 'customer' ? 'selected' : '' }}>Customer (Pembeli)</option>
                </select>
                @error('role')
                    <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Kata Sandi (Min. 6 Karakter) *</label>
                <input type="password" id="password" name="password" required
                       placeholder="••••••••"
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-red-500 focus:bg-white transition-all">
                @error('password')
                    <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                <a href="{{ route('users.index') }}" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl text-xs font-bold shadow-sm transition-colors">
                    Simpan User Baru
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
