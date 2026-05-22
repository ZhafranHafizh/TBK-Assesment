# 07 - Implementation Priority & Acceptance Criteria

## Prioritas Implementasi

### P0 - Wajib Dasar
Kerjakan terlebih dahulu:
- Migration
- Model
- Relationship
- Seeder
- CRUD Kategori COA
- CRUD COA
- CRUD Transaksi
- Validasi debit/credit

Fitur P0 harus stabil sebelum lanjut ke laporan.

---

### P1 - Wajib Utama
Setelah CRUD stabil, kerjakan:
- Laporan Profit/Loss
- Filter periode laporan
- Perhitungan Total Income
- Perhitungan Total Expense
- Perhitungan Net Income
- Export Excel sesuai filter laporan

P1 adalah bagian paling penting untuk penilaian tugas.

---

### P2 - Nilai Tambah
Jika P0 dan P1 sudah selesai:
- Dashboard analytics sederhana
- Cards income/expense/net income bulan ini
- Grafik tren net income per bulan

---

### P3 - Optional
Hanya kerjakan jika masih ada waktu:
- Search advanced
- Pagination advanced
- Styling Excel lebih rapi
- Authentication user
- Role permission

## Acceptance Criteria

### Master Kategori COA
- User bisa melihat daftar kategori.
- User bisa menambah kategori dengan name dan type.
- User bisa mengedit kategori.
- User bisa menghapus kategori jika belum digunakan.
- Type hanya boleh `income` atau `expense`.

### Master COA
- User bisa melihat daftar COA.
- User bisa menambah COA dengan code, name, dan category.
- Code COA unique.
- User bisa mengedit COA.
- User bisa menghapus COA jika belum digunakan transaksi.

### Transaksi
- User bisa melihat daftar transaksi.
- User bisa menambah transaksi.
- User bisa mengedit transaksi.
- User bisa menghapus transaksi.
- Debit dan credit divalidasi sesuai rule.
- COA yang dipilih terhubung ke kategori.

### Laporan Profit/Loss
- User bisa memilih periode laporan.
- Sistem menampilkan bulan sebagai kolom.
- Sistem menampilkan kategori sebagai baris.
- Sistem menghitung income sesuai rule `credit - debit`.
- Sistem menghitung expense sesuai rule `debit - credit`.
- Sistem menghitung Total Income.
- Sistem menghitung Total Expense.
- Sistem menghitung Net Income.

### Export Excel
- User bisa export laporan ke `.xlsx`.
- File export mengikuti filter periode yang aktif.
- File memuat kategori, bulan, Total Income, Total Expense, dan Net Income.

### Dashboard
- Dashboard menampilkan total income bulan ini.
- Dashboard menampilkan total expense bulan ini.
- Dashboard menampilkan net income bulan ini.
- Dashboard menampilkan grafik tren net income bulanan.

## Definition of Done
Sebuah fitur dianggap selesai jika:
- Backend validation berjalan.
- Frontend menampilkan error validasi.
- Data berhasil tersimpan di database.
- UI tidak rusak di desktop dan mobile.
- Tidak ada error console/browser yang mengganggu.
- Data contoh dari seeder bisa digunakan untuk demo.

## Testing Manual Minimal
Lakukan skenario berikut:

1. Tambahkan kategori income.
2. Tambahkan kategori expense.
3. Tambahkan COA dengan kategori income.
4. Tambahkan COA dengan kategori expense.
5. Tambahkan transaksi income dengan credit > 0.
6. Tambahkan transaksi expense dengan debit > 0.
7. Coba input debit dan credit bersamaan, pastikan ditolak.
8. Coba input debit dan credit sama-sama 0, pastikan ditolak.
9. Buka report dan cek total income/expense/net income.
10. Export Excel dan pastikan data sama dengan tampilan report.
