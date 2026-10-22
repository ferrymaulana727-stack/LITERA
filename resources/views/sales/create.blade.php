@extends('layouts.admin')

@section('title', 'Transaksi Kasir Baru')
@section('page-title', 'Form Input Penjualan Kasir')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <a href="{{ route('sales.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-slate-800 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            <span>Kembali ke Riwayat Penjualan</span>
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 md:p-8">
        <div class="mb-6 pb-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-900">Transaksi Kasir Toko Buku & ATK</h3>
                <p class="text-xs text-slate-500 mt-0.5">Perhitungan harga dihitung langsung dari server. Stok buku akan dipotong otomatis setelah nota diterbitkan.</p>
            </div>
            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700">
                Proteksi Transaksi Server
            </span>
        </div>

        <form action="{{ route('sales.store') }}" method="POST" id="saleForm" class="space-y-6">
            @csrf

            <!-- Customer Selection -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="user_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Akun Pembeli / Customer</label>
                    <select id="user_id" name="user_id"
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-red-500 focus:bg-white transition-all">
                        <option value="">-- Pembeli Langsung (Kasir: {{ auth()->user()->name }}) --</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" {{ old('user_id') == $c->id ? 'selected' : '' }}>
                                {{ $c->name }} ({{ $c->email }})
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-slate-400 mt-1">Kosongkan jika transaksi walk-in kasir umum.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Waktu Transaksi</label>
                    <input type="text" value="{{ now()->translatedFormat('d F Y, H:i') }} WIB" readonly
                           class="w-full bg-slate-100 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-600 font-medium">
                </div>
            </div>

            <!-- Items Section -->
            <div class="space-y-3 pt-4 border-t border-slate-100">
                <div class="flex items-center justify-between">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700">Item Buku / ATK yang Dibeli *</h4>
                    <button type="button" id="addRowBtn" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold rounded-lg transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span>Tambah Item Buku</span>
                    </button>
                </div>

                <div class="overflow-x-auto rounded-xl border border-slate-200">
                    <table class="w-full text-left text-xs" id="itemsTable">
                        <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200">
                            <tr>
                                <th class="px-4 py-3 min-w-[260px]">Pilih Produk (Judul / SKU)</th>
                                <th class="px-4 py-3 w-32 text-center">Jumlah Beli</th>
                                <th class="px-4 py-3 w-36 text-right">Harga Satuan (Rp)</th>
                                <th class="px-4 py-3 w-36 text-right">Subtotal</th>
                                <th class="px-3 py-3 w-12 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium" id="itemsContainer">
                            <!-- JS injected rows -->
                        </tbody>
                        <tfoot class="bg-slate-50/80 font-bold border-t border-slate-200">
                            <tr>
                                <td colspan="3" class="px-4 py-3.5 text-right uppercase text-slate-600">Total Belanja :</td>
                                <td class="px-4 py-3.5 text-right font-mono text-sm text-red-600" id="grandTotalDisplay">Rp 0</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                <a href="{{ route('sales.index') }}" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl text-xs font-bold shadow-sm transition-colors">
                    Proses Pembayaran & Cetak Nota
                </button>
            </div>
        </form>
    </div>

</div>

<!-- Products JSON for JS autocomplete -->
<script>
    const availableProducts = @json($products);
    const container = document.getElementById('itemsContainer');
    let rowIndex = 0;

    function addRow(productId = '', quantity = 1) {
        const tr = document.createElement('tr');
        tr.className = 'hover:bg-slate-50/50 transition-colors';
        tr.dataset.index = rowIndex;

        let options = '<option value="">-- Pilih Buku / ATK --</option>';
        availableProducts.forEach(p => {
            const selected = (p.id == productId) ? 'selected' : '';
            options += `<option value="${p.id}" data-price="${p.selling_price}" data-stock="${p.stock}" ${selected}>${p.sku} - ${p.name} (Tersisa: ${p.stock})</option>`;
        });

        tr.innerHTML = `
            <td class="px-4 py-2.5">
                <select name="items[${rowIndex}][product_id]" required class="product-select w-full bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs text-slate-900 focus:outline-none focus:border-red-500">
                    ${options}
                </select>
                <span class="stock-hint text-[10px] text-slate-400 block mt-0.5"></span>
            </td>
            <td class="px-4 py-2.5 text-center">
                <input type="number" name="items[${rowIndex}][quantity]" value="${quantity}" min="1" required class="qty-input w-20 text-center bg-slate-50 border border-slate-200 rounded-lg px-2 py-1.5 text-xs text-slate-900 focus:outline-none focus:border-red-500 font-bold">
            </td>
            <td class="px-4 py-2.5 text-right font-mono text-slate-600 price-display">
                Rp 0
            </td>
            <td class="px-4 py-2.5 text-right font-mono font-bold text-slate-900 subtotal-display">
                Rp 0
            </td>
            <td class="px-3 py-2.5 text-center">
                <button type="button" class="remove-btn p-1 text-slate-400 hover:text-rose-600 rounded">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </button>
            </td>
        `;

        container.appendChild(tr);

        const select = tr.querySelector('.product-select');
        const qtyInput = tr.querySelector('.qty-input');
        const removeBtn = tr.querySelector('.remove-btn');

        select.addEventListener('change', () => calculateRow(tr));
        qtyInput.addEventListener('input', () => calculateRow(tr));
        removeBtn.addEventListener('click', () => {
            if (container.children.length > 1) {
                tr.remove();
                calculateTotal();
            } else {
                alert('Transaksi minimal membutuhkan 1 item buku / ATK.');
            }
        });

        calculateRow(tr);
        rowIndex++;
    }

    function calculateRow(tr) {
        const select = tr.querySelector('.product-select');
        const qtyInput = tr.querySelector('.qty-input');
        const priceDisplay = tr.querySelector('.price-display');
        const subtotalDisplay = tr.querySelector('.subtotal-display');
        const stockHint = tr.querySelector('.stock-hint');

        const selectedOpt = select.options[select.selectedIndex];
        let price = 0;
        let maxStock = 0;

        if (selectedOpt && selectedOpt.dataset.price) {
            price = parseFloat(selectedOpt.dataset.price) || 0;
            maxStock = parseInt(selectedOpt.dataset.stock) || 0;
            stockHint.textContent = `Tersedia di etalase: ${maxStock} pcs`;
            qtyInput.max = maxStock;
        } else {
            stockHint.textContent = '';
        }

        const qty = parseInt(qtyInput.value) || 0;

        if (maxStock > 0 && qty > maxStock) {
            stockHint.innerHTML = `<span class="text-rose-600 font-bold">⚠️ Melebihi stok yang ada (${maxStock})!</span>`;
        }

        const subtotal = qty * price;
        priceDisplay.textContent = 'Rp ' + price.toLocaleString('id-ID');
        subtotalDisplay.textContent = 'Rp ' + subtotal.toLocaleString('id-ID');

        calculateTotal();
    }

    function calculateTotal() {
        let grandTotal = 0;
        document.querySelectorAll('#itemsContainer tr').forEach(tr => {
            const select = tr.querySelector('.product-select');
            const selectedOpt = select.options[select.selectedIndex];
            const price = (selectedOpt && selectedOpt.dataset.price) ? parseFloat(selectedOpt.dataset.price) : 0;
            const qty = parseInt(tr.querySelector('.qty-input').value) || 0;
            grandTotal += (qty * price);
        });
        document.getElementById('grandTotalDisplay').textContent = 'Rp ' + grandTotal.toLocaleString('id-ID');
    }

    document.getElementById('addRowBtn').addEventListener('click', () => addRow());

    // Add first row
    addRow();
</script>
@endsection
