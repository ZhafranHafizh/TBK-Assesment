# 04 - CRUD & Transaction Feature Rules

## M1 - Master Kategori COA

### Halaman Index
Tampilkan daftar kategori COA dengan kolom:
- Name
- Type
- Jumlah COA terkait, optional
- Action: Edit, Delete

### Form Create/Edit
Field:
- Name
- Type: dropdown `income` atau `expense`

### Rules
- `name` wajib diisi.
- `type` wajib dipilih.
- Kategori yang sudah digunakan oleh COA tidak boleh dihapus.
- Gunakan modal konfirmasi saat delete.

---

## M1 - Master Chart of Account (COA)

### Halaman Index
Tampilkan daftar COA dengan kolom:
- Code
- Name
- Category
- Category Type
- Action: Edit, Delete

### Form Create/Edit
Field:
- Code
- Name
- Category COA

### Rules
- `code` wajib unique.
- `name` wajib diisi.
- `coa_category_id` wajib dipilih.
- COA yang sudah digunakan transaksi tidak boleh dihapus.

### UX
Saat memilih category, tampilkan type category sebagai badge:
- Income
- Expense

---

## M2 - Transaksi Keuangan

### Halaman Index
Tampilkan daftar transaksi dengan kolom:
- Date
- COA Code
- COA Name
- Category
- Description
- Debit
- Credit
- Action: Edit, Delete

### Filter Index
Minimal filter:
- Date range atau month range
- COA

Optional filter:
- Category
- Type income/expense
- Search description

### Form Create/Edit
Field:
- Transaction Date
- COA
- Description
- Debit
- Credit

### COA Selection UX
Saat COA dipilih, tampilkan informasi terkait:
- Code
- COA Name
- Category
- Category Type

Contoh tampilan kecil:
```txt
Selected COA: 602 - Bensin
Category: Transport Expense / Expense
```

### Debit/Credit UX
- User hanya boleh mengisi salah satu dari debit atau credit.
- Jika debit diisi > 0, credit otomatis 0.
- Jika credit diisi > 0, debit otomatis 0.
- Tetap lakukan validasi backend.

### Format Angka
- Input boleh plain number.
- Display angka gunakan format ribuan.
- Simpan ke database dalam format decimal numeric, bukan string dengan titik/koma.

### Empty State
Jika belum ada data, tampilkan empty state yang jelas:
```txt
Belum ada transaksi. Tambahkan transaksi pertama untuk mulai membuat laporan Profit/Loss.
```

### Success Message
Setelah create/update/delete berhasil, tampilkan flash message singkat:
- Kategori berhasil ditambahkan.
- COA berhasil diperbarui.
- Transaksi berhasil dihapus.

## Pagination
Gunakan pagination untuk table index jika data sudah banyak. Minimal 10-15 item per halaman.

## Search & Sorting Optional
Jika sempat, tambahkan:
- Search by COA name/code/description
- Sort by date terbaru
