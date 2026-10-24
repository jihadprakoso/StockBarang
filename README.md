# StockBarang

Aplikasi manajemen stok barang sederhana berbasis **PHP Native** dan **MySQL**, dibangun di atas template SB Admin (Bootstrap 5).

## Fitur

- Autentikasi admin dengan `password_hash()` (BCRYPT) dan `password_verify()`.
- Dashboard ringkasan: jumlah jenis barang, total stok, jumlah transaksi masuk/keluar.
- Grafik batang (Chart.js) untuk 6 barang dengan stok terbanyak.
- CRUD barang: tambah, edit, hapus (dengan proteksi CSRF dan prepared statement).
- Transaksi barang masuk & keluar dengan riwayat lengkap (join ke master barang).
- Validasi stok: barang keluar ditolak jika jumlah melebihi stok tersedia.
- Operasi tulis barang masuk/keluar dibungkus transaksi (ACID) agar stok dan riwayat selalu konsisten.
- Seluruh query memakai **prepared statement** (anti SQL Injection) dan output di-escape dengan `htmlspecialchars` (anti XSS).

## Kebutuhan

- PHP 7.4+ (disarankan 8.x) dengan ekstensi `mysqli` dan `mysqlnd`.
- MySQL / MariaDB.

## Instalasi

1. Jalankan MySQL, lalu impor skema dan data contoh:

   ```bash
   mysql -uroot < database.sql
   ```

   Perintah di atas membuat database `stockbarang_local` berisi tabel `login`, `stock`, `masuk`, dan `keluar`.

2. Sesuaikan kredensial database bila perlu di bagian atas `function.php`:

   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'stockbarang_local');
   ```

3. Letakkan folder ini di document root web server, lalu akses `login.php`.

## Akun Demo

| Email | Password |
| --- | --- |
| `admin@stockbarang.local` | `admin` |

## Struktur Berkas

| Berkas | Keterangan |
| --- | --- |
| `function.php` | Koneksi database, helper keamanan (CSRF, escape), dan seluruh aksi CRUD. |
| `cek.php` | Guard sesi: mengalihkan ke `login.php` bila belum masuk. |
| `login.php` / `logout.php` | Masuk dan keluar aplikasi. |
| `index.php` | Dashboard + daftar barang + grafik. |
| `masuk.php` | Riwayat dan input barang masuk. |
| `keluar.php` | Riwayat dan input barang keluar. |
| `database.sql` | Skema dan data contoh. |
