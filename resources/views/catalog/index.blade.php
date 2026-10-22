@extends('layouts.customer')

@section('title', 'Katalog Buku & Alat Tulis')

@section('content')
<div class="space-y-6">

    <!-- Mobile Search Bar (Visible on mobile screens) -->
    <div class="sm:hidden">
        <form action="{{ route('catalog.index') }}" method="GET" class="relative">
            <input type="text" name="search" value="{{ $search }}"
                   placeholder="Cari buku, komik, atau alat tulis..."
                   class="w-full bg-white text-xs text-slate-900 rounded-xl pl-9 pr-4 py-2.5 border border-slate-200 focus:border-red-500 focus:ring-1 focus:ring-red-500 outline-none shadow-xs">
            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            @if($categoryId)
                <input type="hidden" name="category_id" value="{{ $categoryId }}">
            @endif
        </form>
    </div>

    <!-- Hero / Promo Banner Ala Gramedia -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-red-900 via-red-800 to-slate-900 text-white p-5 sm:p-8 shadow-sm">
        <div class="relative z-10 max-w-xl">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] sm:text-xs font-extrabold uppercase tracking-wider bg-red-600/60 text-white mb-2">
                🌟 Koleksi Pilihan Gramedia
            </span>
            <h2 class="text-xl sm:text-3xl font-black tracking-tight leading-tight">
                Jelajahi Buku Terbaik & Perlengkapan Belajar
            </h2>
            <p class="text-xs sm:text-sm text-slate-200 mt-1.5 sm:mt-2">
                Temukan buku fiksi, teknologi pemrograman, self-improvement, serta stationary original dengan stok ter-update.
            </p>
        </div>
        <!-- Decorative background shapes -->
        <div class="absolute -right-8 -bottom-12 w-48 h-48 rounded-full bg-red-600/20 blur-2xl"></div>
        <div class="absolute right-16 -top-10 w-40 h-40 rounded-full bg-blue-600/10 blur-2xl"></div>
    </div>

    <!-- Category Filter Tabs / Pills (Scrollable on mobile) -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1.5 no-scrollbar text-xs font-semibold">
        <a href="{{ route('catalog.index', array_filter(['search' => $search])) }}"
           class="px-4 py-2 rounded-full whitespace-nowrap transition-all shadow-xs {{ empty($categoryId) ? 'bg-red-700 text-white font-bold' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200' }}">
            Semua Koleksi
        </a>
        @foreach($categories as $cat)
            <a href="{{ route('catalog.index', array_filter(['category_id' => $cat->id, 'search' => $search])) }}"
               class="px-4 py-2 rounded-full whitespace-nowrap transition-all shadow-xs {{ $categoryId == $cat->id ? 'bg-red-700 text-white font-bold' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200' }}">
                {{ $cat->name }}
                <span class="opacity-70 text-[10px]">({{ $cat->products_count }})</span>
            </a>
        @endforeach
    </div>

    <!-- Search / Filter Info -->
    @if($search || $categoryId)
        <div class="flex items-center justify-between text-xs text-slate-500 bg-white px-4 py-2.5 rounded-xl border border-slate-200">
            <span>
                Menampilkan hasil:
                @if($search) <strong>"{{ $search }}"</strong> @endif
                @if($categoryId)
                    kategori <strong>{{ $categories->firstWhere('id', $categoryId)->name ?? '' }}</strong>
                @endif
            </span>
            <a href="{{ route('catalog.index') }}" class="text-red-600 font-bold hover:underline">Reset</a>
        </div>
    @endif

    <!-- 2-Column Mobile / 4-Column Desktop Product Cards Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-5">
        @forelse($products as $prod)
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs hover:shadow-md hover:border-slate-300 transition-all flex flex-col justify-between overflow-hidden group">
                <!-- Book Card Cover Image -->
                <div class="relative bg-slate-100 aspect-[3/4] overflow-hidden border-b border-slate-100 flex items-center justify-center">
                    <img src="{{ $prod->image_url ?: asset('images/products/default-book.svg') }}" 
                         alt="{{ $prod->name }}" 
                         loading="lazy"
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">

                    <!-- Stock Status Badge -->
                    <div class="absolute top-2.5 right-2.5">
                        @if($prod->stock <= 0)
                            <span class="px-2 py-0.5 rounded-full text-[9px] font-extrabold uppercase bg-rose-600 text-white shadow-xs">
                                Habis
                            </span>
                        @elseif($prod->isLowStock())
                            <span class="px-2 py-0.5 rounded-full text-[9px] font-extrabold uppercase bg-amber-500 text-white shadow-xs">
                                Sisa {{ $prod->stock }}!
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-emerald-100 text-emerald-800 backdrop-blur-xs">
                                Stok: {{ $prod->stock }}
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Product Info & Actions -->
                <div class="p-3 sm:p-4 flex-1 flex flex-col justify-between">
                    <div>
                        <span class="text-[10px] font-bold text-red-600 tracking-wide uppercase block truncate">
                            {{ $prod->category->name ?? 'Koleksi Umum' }}
                        </span>
                        <h3 class="font-bold text-xs sm:text-sm text-slate-900 mt-1 line-clamp-2 leading-snug group-hover:text-red-700 transition-colors">
                            {{ $prod->name }}
                        </h3>
                    </div>

                    <div class="mt-3 pt-3 border-t border-slate-100 flex flex-col gap-2">
                        <div>
                            <span class="text-[10px] text-slate-400 block leading-none">Harga</span>
                            <span class="text-sm sm:text-base font-extrabold text-slate-900 font-mono">
                                Rp {{ number_format($prod->selling_price, 0, ',', '.') }}
                            </span>
                        </div>

                        <!-- Add to Cart Form -->
                        @if($prod->stock > 0)
                            <form action="{{ route('cart.add') }}" method="POST">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $prod->id }}">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="w-full py-2 px-3 bg-red-600 hover:bg-red-700 text-white text-xs font-bold rounded-xl transition-colors shadow-xs flex items-center justify-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    <span>Beli / Keranjang</span>
                                </button>
                            </form>
                        @else
                            <button type="button" disabled class="w-full py-2 px-3 bg-slate-100 text-slate-400 text-xs font-semibold rounded-xl cursor-not-allowed">
                                Stok Habis
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-16 text-center text-slate-400 bg-white rounded-2xl border border-slate-200">
                <svg class="w-12 h-12 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
                <p class="font-bold text-slate-700 text-sm">Tidak ada buku / ATK ditemukan</p>
                <p class="text-xs text-slate-400 mt-1">Coba cari dengan kata kunci lain atau pilih kategori berbeda.</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($products->hasPages())
        <div class="pt-4">
            {{ $products->links() }}
        </div>
    @endif

</div>

<!-- Floating Shopping Cart Bottom Bar (Ala E-commerce Mobile Modern) -->
@if(!empty($cart))
    <div class="fixed bottom-16 md:bottom-6 left-4 right-4 max-w-xl mx-auto z-40">
        <div class="bg-slate-900 text-white rounded-2xl p-3 sm:p-4 shadow-2xl border border-slate-700/80 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="relative w-10 h-10 rounded-xl bg-red-600 flex items-center justify-center font-bold text-white shadow-md">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                    <span class="absolute -top-1.5 -right-1.5 bg-white text-slate-900 text-[10px] font-black w-5 h-5 rounded-full flex items-center justify-center border-2 border-slate-900">
                        {{ $cartCount }}
                    </span>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 block uppercase tracking-wider font-semibold">Total {{ $cartCount }} Buku di Keranjang</span>
                    <span class="text-sm sm:text-base font-black text-white font-mono">
                        Rp {{ number_format($cartTotal, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" onclick="openCartDrawer()" class="px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold rounded-xl transition-colors">
                    Lihat Isi
                </button>
                <form action="{{ route('checkout') }}" method="POST" onsubmit="return confirm('Konfirmasi Checkout: Seluruh item akan diproses dan stok akan otomatis terpotong. Lanjutkan?')">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-gradient-to-r from-red-600 to-red-500 hover:from-red-500 hover:to-red-600 text-white text-xs font-black rounded-xl shadow-lg shadow-red-950/40 transition-all flex items-center gap-1.5">
                        <span>Checkout</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Slide-over / Modal Cart Drawer -->
    <div id="cartModal" class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs z-50 hidden flex justify-end">
        <div class="bg-white w-full max-w-md h-full flex flex-col shadow-2xl p-6 overflow-y-auto">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-red-100 text-red-600 flex items-center justify-center font-bold">
                        🛒
                    </div>
                    <div>
                        <h3 class="font-extrabold text-slate-900 text-base">Keranjang Belanja</h3>
                        <p class="text-xs text-slate-500">{{ count($cart) }} macam produk dipilih</p>
                    </div>
                </div>
                <button type="button" onclick="closeCartDrawer()" class="p-2 text-slate-400 hover:text-slate-700 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Cart Items List -->
            <div class="flex-1 py-4 divide-y divide-slate-100 overflow-y-auto space-y-3">
                @foreach($cart as $pId => $item)
                    <div class="pt-3 first:pt-0 flex items-start justify-between gap-3">
                        <img src="{{ $item['image_url'] ?? asset('images/products/default-book.svg') }}" 
                             alt="{{ $item['name'] }}" 
                             class="w-12 h-16 object-cover rounded-lg shrink-0 shadow-xs border border-slate-100">
                        <div class="flex-1">
                            <span class="text-[10px] text-slate-400 font-mono">{{ $item['sku'] }}</span>
                            <h4 class="text-xs font-bold text-slate-900 line-clamp-1">{{ $item['name'] }}</h4>
                            <span class="text-xs font-mono font-bold text-red-600 block mt-0.5">
                                Rp {{ number_format($item['price'], 0, ',', '.') }}
                            </span>

                            <!-- Quantity controls -->
                            <div class="flex items-center gap-2 mt-2">
                                <form action="{{ route('cart.update') }}" method="POST" class="flex items-center gap-1">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $item['id'] }}">
                                    <span class="text-[11px] text-slate-500">Qty:</span>
                                    <input type="number" name="quantity" value="{{ $item['quantity'] }}" min="1" max="99"
                                           class="w-14 text-center text-xs font-bold bg-slate-50 border border-slate-200 rounded-lg py-1 px-1.5 focus:border-red-500">
                                    <button type="submit" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-[10px] font-bold">
                                        Update
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="text-right">
                            <span class="text-xs font-mono font-bold text-slate-900 block">
                                Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}
                            </span>
                            <form action="{{ route('cart.remove', $item['id']) }}" method="POST" class="mt-2 inline-block">
                                @csrf
                                <button type="submit" class="text-[10px] font-semibold text-rose-600 hover:underline">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Cart Summary & Checkout -->
            <div class="pt-4 border-t border-slate-100 space-y-4">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-slate-600 font-medium">Total Tagihan:</span>
                    <span class="text-lg font-black text-red-600 font-mono">
                        Rp {{ number_format($cartTotal, 0, ',', '.') }}
                    </span>
                </div>

                <div class="space-y-2">
                    <form action="{{ route('checkout') }}" method="POST" onsubmit="return confirm('Konfirmasi Checkout: Proses pemesanan sekarang?')">
                        @csrf
                        <button type="submit" class="w-full py-3 bg-red-600 hover:bg-red-700 text-white font-bold text-sm rounded-xl shadow-md transition-all flex items-center justify-center gap-2">
                            <span>Beli & Terbitkan Nota Sekarang</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                            </svg>
                        </button>
                    </form>

                    <form action="{{ route('cart.clear') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold text-xs rounded-xl transition-colors">
                            Kosongkan Keranjang
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openCartDrawer() {
            document.getElementById('cartModal').classList.remove('hidden');
        }
        function closeCartDrawer() {
            document.getElementById('cartModal').classList.add('hidden');
        }
    </script>
@endif
@endsection
