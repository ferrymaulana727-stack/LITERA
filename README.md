# 🏛️ LITERA — Toko Buku & Alat Tulis (Ala Gramedia)
**Mata Kuliah:** Web Programming 2 (UTS - Integrated Laravel Project)  
**Tema Sistem:** Sistem Inventori & Penjualan Toko Buku dan Alat Tulis  
**Prinsip Desain:** 100% Mengikuti Spesifikasi [blueprint.md](blueprint.md)  
**Tech Stack:** Laravel (MVC), MySQL, Eloquent ORM, Blade + Tailwind CSS  
**Nama Database:** `gramedia_store_db`

---

## 📌 Ringkasan Implementasi Fitur Sesuai Blueprint

1. **Autentikasi & Otorisasi 3 Role:**
   * Login, Logout, dan Registrasi (khusus akun Customer).
   * Middleware Role Server-Side (`App\Http\Middleware\RoleMiddleware`):
     * **Admin (Owner):** Akses penuh desktop back-office (Dashboard finansial, CRUD Kategori, CRUD Supplier, CRUD Produk + SoftDeletes, Input Kulakan, Input Penjualan, Batalkan Nota Transaksi, dan Kelola User).
     * **Staff (Gudang/Kasir):** Akses desktop back-office untuk melihat ringkasan stok & transaksi, Read-Only pada Master Data, Input Barang Masuk dari Penerbit (Kulakan), dan Input Kasir Penjualan. Dilarang membatalkan nota atau mengelola user.
     * **Customer (Pembeli):** Mobile-first antarmuka etalase katalog toko buku modern (`/catalog`), keranjang belanja, checkout transaksi langsung, dan melihat riwayat belanja miliknya sendiri (`/my-orders`). Ditolak otomatis jika mencoba mengakses back-office.
2. **Integritas Transaksi & Proteksi Database:**
   * `DB::transaction` dan `lockForUpdate` pada setiap transaksi Kulakan (Stock-In) maupun Penjualan (Stock-Out).
   * **Stok Berkurang / Bertambah Otomatis:**
     * Kulakan (Stock-In) $\rightarrow$ Stok produk bertambah otomatis.
     * Penjualan (Stock-Out) $\rightarrow$ Stok produk berkurang otomatis.
   * **Strict Stock Check & Atomic Rollback:** Jika kuantitas salah satu buku tidak mencukupi, seluruh transaksi dan nota dibatalkan otomatis (rollback).
   * **Server-Side Price Calculation:** Seluruh harga satuan, subtotal, dan grand total dihitung 100% di server dari database, bukan dari input browser.
   * **Pembatalan Nota (Cancel) oleh Admin:**
     * Membatalkan nota kulakan $\rightarrow$ stok barang ditarik kembali secara otomatis.
     * Membatalkan nota penjualan $\rightarrow$ stok barang dikembalikan ke etalase toko secara otomatis.
   * **SoftDeletes:** Produk yang sudah memiliki riwayat transaksi di-soft delete agar integritas historis laporan nota masa lalu tetap valid.
3. **Pencarian, Filter, & Pagination:**
   * Filter kategori, filter status nota, date range filter, dan pencarian SKU / judul buku di semua halaman utama.
4. **Dashboard Agregat:**
   * Query database agregat `count`, `sum`, serta daftar produk berstatus **Low-Stock Alert** (`stock <= minimum_stock`).

---

## 🔑 Akun Demo (Berdasarkan Blueprint Section 4)

| Role | Alamat Email | Password | Hak Akses |
| :--- | :--- | :--- | :--- |
| **Admin (Owner)** | `admin@gramedia.test` | `password` | Back-Office Full + Batalkan Nota + Kelola User |
| **Staff (Gudang/Kasir)** | `staff@gramedia.test` | `password` | Back-Office Kasir & Input Barang Masuk |
| **Customer (Pembeli)** | `customer@gramedia.test` | `password` | Mobile Katalog Belanja & Riwayat Nota |

> **Catatan:** Pada halaman Login, telah disediakan tombol **Akses Cepat 1-Klik** untuk masing-masing akun demo agar memudahkan proses pengujian dan penilaian dosen/asisten lab.

---

## 🗄️ Struktur Database (8 Tabel Inti)

1. **`users`**: `id`, `name`, `email`, `password`, `role` (`admin`, `staff`, `customer`), `timestamps`
2. **`categories`**: `id`, `name`, `timestamps`
3. **`suppliers`**: `id`, `name`, `phone`, `email`, `timestamps`
4. **`products`**: `id`, `category_id`, `sku`, `name`, `purchase_price`, `selling_price`, `stock`, `minimum_stock`, `deleted_at` (*SoftDeletes*), `timestamps`
5. **`purchases`**: `id`, `supplier_id`, `user_id`, `purchase_number`, `total`, `status` (`completed`/`cancelled`), `purchased_at`, `timestamps`
6. **`purchase_items`**: `id`, `purchase_id`, `product_id`, `quantity`, `unit_cost`, `subtotal`, `timestamps`
7. **`sales`**: `id`, `user_id`, `sale_number`, `total`, `status` (`completed`/`cancelled`), `sold_at`, `timestamps`
8. **`sale_items`**: `id`, `sale_id`, `product_id`, `quantity`, `unit_price`, `subtotal`, `timestamps`

---

## 🚀 Cara Menjalankan Project

1. **Pastikan MySQL Aktif:**
   Pastikan service MySQL di XAMPP telah berjalan pada port `3306`.
2. **Migrasi & Seeder Database:**
   ```bash
   php artisan migrate:fresh --seed
   ```
3. **Compile Asset Frontend:**
   ```bash
   npm run build
   ```
4. **Jalankan Development Server:**
   ```bash
   php artisan serve
   ```
5. Buka di browser: `http://localhost:8000`

---

## 🧪 Menjalankan Automated Test

Semua skenario pengujian telah diuji dengan PHPUnit / Pest:
```bash
php artisan test
```
*Hasil: 13 tests passed (66 assertions).*
