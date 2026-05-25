# Aplikasi Pencatatan Keuangan

Sebuah aplikasi web pencatatan keuangan berbasis Chart of Account (COA). Aplikasi ini dirancang untuk mencatat transaksi keuangan secara akurat dengan mengelompokkan setiap transaksi ke dalam kategori Income atau Expense, lalu menghasilkan laporan Profit/Loss (Laba Rugi) bulanan secara instan.

Sistem ini sangat cocok digunakan untuk admin atau pencatat keuangan yang membutuhkan pencatatan debit/kredit harian dengan dukungan multi-currency (konversi mata uang asing otomatis) dan antarmuka yang modern.

## Daftar Modul

Aplikasi ini memiliki beberapa modul utama:

1. **Master Data (COA)**
   - Mengelola data Kategori COA (Income/Expense).
   - Mengelola data Master Chart of Account (COA).
2. **Transaksi Keuangan**
   - Pencatatan transaksi Debit dan Credit harian.
   - Mendukung input nominal dalam Rupiah (IDR) maupun Valuta Asing.
   - Konversi mata uang otomatis (mengambil historical rate maupun latest rate via Frankfurter API).
3. **Laporan Profit/Loss**
   - Laporan keuangan bulanan berbasis pivot/cross-tab.
   - Filter rentang periode yang fleksibel.
4. **Export Data**
   - Mendukung export data laporan ke dalam format Microsoft Excel (.xlsx).
5. **Dashboard Analytics**
   - Menyajikan ringkasan visual mengenai total Income, total Expense, Net Income, serta grafik tren transaksi.
6. **Pengaturan Sistem**
   - Pengaturan konfigurasi nama aplikasi secara global dan pengelolaan mata uang yang diizinkan dalam sistem.

## Keamanan & Integritas Data

Sistem ini dirancang dengan prinsip akuntansi yang ketat untuk mencegah manipulasi data (baik yang disengaja maupun karena *human error*):

- **Auto-Routing Debit/Kredit:** User tidak dapat memilih apakah transaksi masuk ke Debit atau Credit secara manual. Sistem secara otomatis menempatkan nominal berdasarkan Kategori COA yang dipilih (Income otomatis masuk ke kolom Credit, Expense masuk ke kolom Debit).
- **Read-Only Exchange Rate:** Field nilai tukar mata uang asing (*exchange rate*) dikunci (*readonly*) pada antarmuka pengguna. Nilai tukar didapatkan murni dari pemanggilan API secara otomatis berdasarkan tanggal transaksi, sehingga user tidak dapat memanipulasi rate konversi.
- **Periode Kunci (Lock Period):** Data Chart of Account (COA) dan transaksi yang telah melewati batas waktu tertentu (misalnya 24 jam) akan terkunci dan tidak dapat diedit secara bebas untuk menjaga keabsahan riwayat pembukuan.
- **Backend Validation:** Validasi ganda selalu dilakukan di sisi server (Laravel Form Request) untuk memastikan integritas data tetap terjaga meskipun validasi frontend (UI) berhasil di-bypass.
- **Soft Deletes (Jejak Audit):** Setiap data (COA, kategori, atau transaksi) yang dihapus tidak akan benar-benar dihilangkan secara fisik dari database (*hard delete*). Sistem menggunakan mekanisme *soft deletes* sehingga data lama tetap tersimpan secara tersembunyi sebagai jejak audit (*audit trail*) dan dapat dipulihkan kapan saja melalui modul Arsip.

## Tech Stack

Proyek ini dibangun menggunakan teknologi modern:

- **Backend:** Laravel
- **Frontend:** Vue.js 3 (Composition API, Script Setup)
- **Bridge:** Inertia.js
- **Styling:** Tailwind CSS (Dark Mode Neo-Brutalism Theme)
- **Database:** MySQL
- **Library Tambahan:**
  - `maatwebsite/excel` untuk fungsionalitas export Excel.
  - `vue-currency-input` untuk formatting angka mata uang secara real-time.
  - `chart.js` / `vue-chartjs` untuk visualisasi grafik.

## Cara Menjalankan Aplikasi di Local

Ikuti panduan berikut untuk menjalankan aplikasi di lingkungan pengembangan lokal Anda:

### 1. Kebutuhan Sistem
Pastikan Anda sudah menginstal perangkat lunak berikut:
- PHP (minimal versi 8.1 / 8.2)
- Composer
- Node.js & npm
- MySQL Server (XAMPP / Laragon / Native)

### 2. Instalasi
Masuk ke direktori proyek di terminal Anda, lalu instal seluruh dependensi:

```bash
# Instal dependensi PHP (Laravel)
composer install

# Instal dependensi JavaScript (Vue)
npm install
```

### 3. Konfigurasi Environment
Buat salinan dari file konfigurasi environment dan *generate* application key:

```bash
cp .env.example .env
php artisan key:generate
```

Buka file `.env` dan sesuaikan kredensial database Anda:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nama_database_anda
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Migrasi dan Seeder
Jalankan migrasi database untuk membuat struktur tabel sekaligus memasukkan data awal (akun master, pengaturan default, dsb):

```bash
php artisan migrate --seed
```

### 5. Menjalankan Server
Untuk menjalankan aplikasi ini, Anda harus mengaktifkan dua server secara bersamaan. Buka dua jendela terminal/command prompt:

Terminal 1 (Backend Server):
```bash
php artisan serve
```

Terminal 2 (Frontend Asset Bundler):
```bash
npm run dev
```

Aplikasi sekarang sudah berjalan dan dapat diakses melalui browser pada alamat: `http://localhost:8000`

### Akun Login (Default Seeder)
Jika Anda menggunakan seeder bawaan proyek, Anda dapat masuk menggunakan:
- **Email:** admin@admin.com (atau sesuai data seeder)
- **Password:** password
