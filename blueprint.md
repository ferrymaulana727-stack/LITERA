# 🏛️ SYSTEM BLUEPRINT: LITERA / GRAMEDIA-STYLE STORE (WAJIB UTS)
**Mata Kuliah:** Web Programming 2 (UTS - Integrated Laravel Project)[cite: 5]  
**Tema Sistem:** Sistem Inventori & Penjualan Toko Buku dan Alat Tulis (Ala Gramedia)[cite: 5]  
**Prinsip:** Fokus 100% pada syarat wajib modul UTS + 3 Role (Admin, Staff, Customer)[cite: 5]  
**Tech Stack:** Laravel (MVC), MySQL, Eloquent ORM, Blade + Tailwind CSS[cite: 5]  
**Nama Database:** `gramedia_store_db`

---

## 1. Lingkup Fitur (Hanya yang Wajib di Modul UTS)

* **Wajib Ada[cite: 5]:**
  * Autentikasi: Login, Logout, dan Register (untuk Customer)[cite: 5].
  * Otorisasi Server-Side (Middleware Role: `admin`, `staff`, `customer`)[cite: 5].
  * Master Data: CRUD Kategori, Produk (Buku/ATK), dan Supplier (Penerbit/Distributor)[cite: 5].
  * Transaksi Pembelian (*Stock-In* dari Penerbit) $\rightarrow$ Stok bertambah otomatis[cite: 5].
  * Transaksi Penjualan (*Stock-Out* / Belanja Customer & Kasir) $\rightarrow$ Stok berkurang otomatis[cite: 5].
  * Proteksi `DB::transaction` & `lockForUpdate` (Jika 1 buku stoknya kurang, seluruh nota batal/rollback)[cite: 5].
  * Perhitungan harga & total wajib dilakukan di Server, bukan dari browser[cite: 5].
  * Pembatalan transaksi (*Cancel*) oleh Admin yang memulihkan stok secara otomatis[cite: 5].
  * Larangan *hard-delete* produk yang sudah pernah ditransaksikan (menggunakan *SoftDeletes*)[cite: 5].
  * Pencarian (SKU/Judul Buku), Filter Kategori/Tanggal/Status, dan Pagination[cite: 5].
  * Dashboard ringkas dengan query agregat database (`count`, `sum`, dan daftar *low-stock*)[cite: 5].

* **Tidak Dibuat (Karena Tidak Wajib di Modul)[cite: 5]:**
  * ❌ Payment Gateway nyata (QRIS/Midtrans/Transfer Bank)[cite: 5].
  * ❌ Perhitungan ongkos kirim kurir / ekspedisi.
  * ❌ Multi-cabang toko / multi-gudang[cite: 5].
  * ❌ Laporan akuntansi laba-rugi kompleks[cite: 5].

---

## 2. Pembagian Hak Akses 3 Role (Server-Side Protection)

| Modul / Halaman | Admin (Owner)[cite: 5] | Staff (Gudang/Kasir)[cite: 5] | Customer (Pembeli) |
| :--- | :---: | :---: | :---: |
| **Fokus Tampilan Layar** | Desktop Back-Office | Desktop Back-Office | **Mobile-First Katalog Buku** (Responsif Desktop) |
| **Katalog & Belanja (`/catalog`)** | ✅ Bisa Lihat | ✅ Bisa Lihat | ✅ **Halaman Utama (Pilih Buku & Checkout)** |
| **Riwayat Belanja (`/my-orders`)** | ✅ | ✅ | ✅ **Hanya melihat nota miliknya sendiri** |
| **Dashboard (`/dashboard`)**[cite: 5] | ✅ Total Produk, Omzet, Transaksi, Stok Rendah[cite: 5] | ✅ Ringkasan Stok & Transaksi[cite: 5] | ❌ Ditolak (`403 Forbidden`) |
| **Kategori & Supplier (Penerbit)**[cite: 5] | ✅ Full CRUD (Tambah/Edit/Hapus)[cite: 5] | 👁️ Read-Only (Hanya Lihat)[cite: 5] | ❌ Ditolak (`403`) |
| **Produk (Buku & ATK)**[cite: 5] | ✅ Full CRUD + Soft Delete[cite: 5] | 👁️ Read-Only (Lihat daftar & stok)[cite: 5] | ❌ Ditolak (`403` $\rightarrow$ diarahkan ke `/catalog`) |
| **Purchases / Stock-In (`/purchases`)**[cite: 5] | ✅ Lihat Semua, Buat, & **Batalkan Nota**[cite: 5] | ✅ **Input Barang Masuk dari Penerbit**[cite: 5] | ❌ Ditolak (`403`) |
| **Sales / Penjualan (`/sales`)**[cite: 5] | ✅ Lihat Semua, Buat, & **Batalkan Nota**[cite: 5] | ✅ Input Kasir & Lihat Transaksi[cite: 5] | ❌ Ditolak (`403` $\rightarrow$ diarahkan ke `/my-orders`) |
| **Kelola User (`/users`)**[cite: 5] | ✅ Kelola Akun Admin, Staff, Customer[cite: 5] | ❌ Ditolak (`403`)[cite: 5] | ❌ Ditolak (`403`) |

---

## 3. Rancangan Database Minimal (8 Tabel Inti Sesuai Modul)[cite: 5]

1. **`users`**[cite: 5]
   * `id` (PK), `name`, `email` (unique), `password`, `role` (`enum: 'admin', 'staff', 'customer'`), `timestamps`[cite: 5]
2. **`categories`**[cite: 5]
   * `id` (PK), `name` (unique), `timestamps`[cite: 5]
3. **`suppliers`** *(Penerbit & Distributor ATK)*[cite: 5]
   * `id` (PK), `name`, `phone`, `email`, `timestamps`[cite: 5]
4. **`products`** *(Buku & Alat Tulis)*[cite: 5]
   * `id` (PK), `category_id` (FK), `sku` (unique - Kode Buku/ISBN), `name` (Judul Buku/Nama Barang), `purchase_price`, `selling_price`, `stock`, `minimum_stock`, `deleted_at` (*SoftDeletes*), `timestamps`[cite: 5]
5. **`purchases`** *(Header Nota Kulakan ke Penerbit)*[cite: 5]
   * `id` (PK), `supplier_id` (FK), `user_id` (FK - Staff/Admin penginput), `purchase_number` (unique), `total`, `status` (`completed`/`cancelled`), `purchased_at`, `timestamps`[cite: 5]
6. **`purchase_items`** *(Detail Item Buku yang Dikulak)*[cite: 5]
   * `id` (PK), `purchase_id` (FK), `product_id` (FK), `quantity`, `unit_cost`, `subtotal`, `timestamps`[cite: 5]
7. **`sales`** *(Header Nota Pembelian Customer / Kasir)*[cite: 5]
   * `id` (PK), `user_id` (FK - Customer yang beli / Staff yang melayani), `sale_number` (unique), `total`, `status` (`completed`/`cancelled`), `sold_at`, `timestamps`[cite: 5]
8. **`sale_items`** *(Detail Item Buku yang Dibeli)*[cite: 5]
   * `id` (PK), `sale_id` (FK), `product_id` (FK), `quantity`, `unit_price`, `subtotal`, `timestamps`[cite: 5]

---

## 4. Data Awal (Seeder) Bertema Gramedia[cite: 5]

* **Akun Demo (`UserSeeder`)[cite: 5]:**
  * Admin: `admin@gramedia.test` | Password: `password`[cite: 5]
  * Staff: `staff@gramedia.test` | Password: `password`[cite: 5]
  * Customer: `customer@gramedia.test` | Password: `password`[cite: 5]

* **Kategori (`CategorySeeder` - 4 Kategori)[cite: 5]:**
  1. `Novel & Sastra`
  2. `Buku Komputer & Pemrograman`
  3. `Pengembangan Diri (Self-Improvement)`
  4. `Alat Tulis & Perlengkapan Kantor (ATK)`

* **Supplier / Penerbit (`SupplierSeeder` - 3 Penerbit)[cite: 5]:**
  1. `PT Gramedia Pustaka Utama` (021-53650110 | kontak@gpu.id)
  2. `Penerbit Erlangga & Elex Media` (021-8717006 | info@erlangga.co.id)
  3. `PT Faber-Castell & Joyko Stationary` (021-4601234 | supply@stationary.co.id)

* **Produk Buku & ATK (`ProductSeeder` - 6 Produk Contoh)[cite: 5]:**
  | Kode SKU | Judul Buku / Nama Produk | Kategori | Harga Beli | Harga Jual | Stok | Min. Stok |
  | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
  | `BKU-NVL-01` | Laut Bercerita - Leila S. Chudori | Novel & Sastra | Rp 75.000 | Rp 115.000 | 20 | 5 |
  | `BKU-NVL-02` | Bumi Manusia - Pramoedya A. Toer | Novel & Sastra | Rp 85.000 | Rp 135.000 | 12 | 5 |
  | `BKU-TEK-01` | Mastering Laravel 11 & Eloquent ORM | Buku Komputer | Rp 90.000 | Rp 145.000 | **3 (Low)** | 5 |
  | `BKU-SLF-01` | Atomic Habits (Edisi Bahasa Indonesia) | Pengembangan Diri | Rp 70.000 | Rp 108.000 | 18 | 5 |
  | `ATK-KRT-01` | Buku Catatan Loose Leaf B5 Campus | Alat Tulis (ATK) | Rp 15.000 | Rp 28.000 | 40 | 10 |
  | `ATK-PEN-01` | Paket Pulpen Gel Sarasa Clip 0.5 Hitam | Alat Tulis (ATK) | Rp 55.000 | Rp 85.000 | **4 (Low)** | 6 |

---

## 5. Gambaran Tampilan (Mobile Customer vs Desktop Admin)

* **Tampilan Customer (Prioritas Mobile-First di HP):**
  * Warna bersih khas toko buku modern: Background putih bersih (`#F8FAFC`), teks gelap nyaman dibaca, dengan aksen **Biru Navy / Merah Marun elegan khas Gramedia**.
  * Begitu Customer login lewat HP, langsung tampil **Katalog Buku & ATK** berbentuk kartu 2 kolom, lengkap dengan kolom pencarian judul/SKU, tombol filter kategori (*Novel, Komputer, ATK*), serta keranjang belanja sederhana untuk *Checkout* beberapa buku sekaligus.
* **Tampilan Admin & Staff (Desktop Back-Office):**
  * Sidebar di sebelah kiri untuk berpindah menu (*Dashboard, Kategori, Produk Buku, Penerbit/Supplier, Stock-In Pembelian, Riwayat Penjualan*).