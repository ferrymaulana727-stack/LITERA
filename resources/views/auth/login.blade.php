<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - Litera Gramedia Store</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="h-full flex items-center justify-center p-4 bg-slate-950 font-sans antialiased text-slate-100">

    <div class="w-full max-w-md">
        <!-- Brand Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-tr from-red-700 to-red-500 text-white font-black text-2xl shadow-xl shadow-red-950/60 mb-3">
                L
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight">LITERA STORE</h1>
            <p class="text-sm text-slate-400 mt-1">Sistem Inventori & Penjualan Toko Buku (UTS Project)</p>
        </div>

        <!-- Form Card -->
        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-6 sm:p-8 shadow-2xl backdrop-blur-sm">
            <h2 class="text-lg font-bold text-white mb-2">Masuk ke Akun Anda</h2>
            <p class="text-xs text-slate-400 mb-6">Silakan masukkan email dan password untuk melanjutkan.</p>

            @if(session('success'))
                <div class="mb-5 p-3 rounded-xl bg-emerald-950/60 border border-emerald-800/80 text-emerald-300 text-xs font-medium">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('info'))
                <div class="mb-5 p-3 rounded-xl bg-blue-950/60 border border-blue-800/80 text-blue-300 text-xs font-medium">
                    {{ session('info') }}
                </div>
            @endif

            @if(session('warning'))
                <div class="mb-5 p-3 rounded-xl bg-amber-950/60 border border-amber-800/80 text-amber-300 text-xs font-medium">
                    {{ session('warning') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-5 p-3 rounded-xl bg-rose-950/60 border border-rose-800/80 text-rose-300 text-xs">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-300 mb-1.5">Alamat Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                           placeholder="nama@gramedia.test"
                           class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-3.5 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500 transition-all">
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-300 mb-1.5">Kata Sandi</label>
                    <input type="password" id="password" name="password" required
                           placeholder="••••••••"
                           class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-3.5 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500 transition-all">
                </div>

                <div class="flex items-center justify-between text-xs pt-1">
                    <label class="flex items-center gap-2 cursor-pointer text-slate-400 hover:text-slate-200">
                        <input type="checkbox" name="remember" class="rounded bg-slate-950 border-slate-700 text-red-600 focus:ring-red-500">
                        <span>Ingat saya di perangkat ini</span>
                    </label>
                </div>

                <button type="submit" class="w-full py-2.5 px-4 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-500 hover:to-red-600 text-white font-bold text-sm rounded-xl shadow-lg shadow-red-900/30 transition-all">
                    Masuk Sekarang
                </button>
            </form>

            <!-- Quick Demo Login Buttons (Per Blueprint Section 4) -->
            <div class="mt-6 pt-5 border-t border-slate-800">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2.5 text-center">
                    Akses Cepat Akun Demo (Blueprint)
                </p>
                <div class="grid grid-cols-3 gap-2">
                    <button type="button" onclick="fillCredentials('admin@gramedia.test', 'password')"
                            class="px-2 py-2 bg-slate-800 hover:bg-red-950/40 hover:border-red-600/50 border border-slate-700/80 rounded-lg text-left transition-all group">
                        <span class="block text-[10px] font-extrabold text-red-400 group-hover:text-red-300">ADMIN</span>
                        <span class="block text-[10px] text-slate-400 truncate">Owner Toko</span>
                    </button>

                    <button type="button" onclick="fillCredentials('staff@gramedia.test', 'password')"
                            class="px-2 py-2 bg-slate-800 hover:bg-blue-950/40 hover:border-blue-600/50 border border-slate-700/80 rounded-lg text-left transition-all group">
                        <span class="block text-[10px] font-extrabold text-blue-400 group-hover:text-blue-300">STAFF</span>
                        <span class="block text-[10px] text-slate-400 truncate">Gudang/Kasir</span>
                    </button>

                    <button type="button" onclick="fillCredentials('customer@gramedia.test', 'password')"
                            class="px-2 py-2 bg-slate-800 hover:bg-emerald-950/40 hover:border-emerald-600/50 border border-slate-700/80 rounded-lg text-left transition-all group">
                        <span class="block text-[10px] font-extrabold text-emerald-400 group-hover:text-emerald-300">CUSTOMER</span>
                        <span class="block text-[10px] text-slate-400 truncate">Pembeli</span>
                    </button>
                </div>
            </div>

            <div class="mt-6 text-center text-xs text-slate-400">
                Belum punya akun Customer?
                <a href="{{ route('register') }}" class="font-bold text-red-400 hover:text-red-300 ml-1">Daftar di sini</a>
            </div>
        </div>

        <div class="text-center mt-6">
            <a href="{{ route('catalog.index') }}" class="inline-flex items-center gap-1.5 text-xs text-slate-400 hover:text-slate-300 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span>Kembali ke Katalog Toko</span>
            </a>
        </div>
    </div>

    <script>
        function fillCredentials(email, password) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = password;
        }
    </script>
</body>
</html>
