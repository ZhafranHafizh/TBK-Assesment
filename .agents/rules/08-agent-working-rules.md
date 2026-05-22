# 08 - Agent Working Rules

## Tujuan File Ini
File ini berisi aturan kerja untuk agent coder saat mengimplementasikan project. Ikuti aturan ini agar hasil implementasi rapi, konsisten, dan tidak keluar dari scope tugas.

## General Rules
- Jangan mengubah tech stack utama.
- Jangan menambahkan fitur besar di luar PRD tanpa kebutuhan jelas.
- Prioritaskan CRUD, transaksi, laporan, dan export.
- Dashboard hanya dikerjakan setelah fitur inti selesai.
- Jangan membuat logic laporan menumpuk di controller.
- Jangan melewati validasi backend.
- Jangan menyimpan angka uang sebagai string berformat titik/koma.

## Coding Rules Laravel
- Gunakan Laravel convention.
- Gunakan migration, model, factory/seeder jika dibutuhkan.
- Gunakan Form Request untuk validasi.
- Gunakan Eloquent relationship.
- Gunakan service class untuk logic report.
- Gunakan policy/authorization hanya jika auth diterapkan.
- Gunakan flash message untuk feedback CRUD.

## Coding Rules Vue/Inertia
- Gunakan Vue 3 Composition API jika memungkinkan.
- Pisahkan komponen yang reusable.
- Gunakan props dari Inertia secara jelas.
- Jangan membuat satu file Vue terlalu panjang jika bisa dipisah.
- Gunakan state loading untuk submit form.
- Tampilkan validation error dari Laravel.

## Data Formatting Rules
- Database menyimpan decimal numeric.
- UI menampilkan angka dengan format ribuan.
- Date disimpan sebagai `YYYY-MM-DD`.
- Month filter menggunakan `YYYY-MM`.
- Excel export mengikuti filter yang sedang aktif.

## Report Calculation Rules
Jangan mengubah rumus berikut:

Income:
```txt
total_credit - total_debit
```

Expense:
```txt
total_debit - total_credit
```

Net Income:
```txt
Total Income - Total Expense
```

## UI Rules
- Gunakan desain modern minimalis.
- Fokus ke readability.
- Table angka harus align right.
- Error validasi harus terlihat jelas.
- Form jangan terlalu padat.
- Tambahkan empty state untuk data kosong.
- Tambahkan confirm modal sebelum delete.

## Git/Commit Suggestion
Gunakan commit kecil per modul:
```txt
feat: add coa category crud
feat: add chart of account crud
feat: add financial transaction crud
feat: add profit loss report
feat: add excel export
feat: add dashboard analytics
```

## Jangan Lakukan
- Jangan hardcode report hanya untuk data contoh.
- Jangan hitung Profit/Loss di frontend saja.
- Jangan export semua data jika user sedang menggunakan filter tertentu.
- Jangan menghapus kategori/COA yang sudah memiliki relasi.
- Jangan membuat dashboard sebelum report selesai.
- Jangan mencampur bahasa field database antara Indonesia dan Inggris.

## Final Output Expected
Project selesai harus memiliki:
- Migration lengkap
- Seeder data contoh
- CRUD Category
- CRUD COA
- CRUD Transaction
- Profit/Loss Report
- Export Excel
- Dashboard sederhana
- UI rapi dan responsive
