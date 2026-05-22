# 03 - Business Rules & Validation Rules

## Business Rules Utama

### BR-01 - Kategori COA
Setiap kategori COA wajib memiliki tipe:
- `income`
- `expense`

Field `type` digunakan untuk menentukan cara menghitung nilai laporan Profit/Loss.

---

### BR-02 - Perhitungan Income
Untuk kategori bertipe `income`, nilai laporan dihitung dengan rumus:

```txt
income_amount = total_credit - total_debit
```

Contoh:
Jika Salary memiliki total credit 12.000.000 dan total debit 0, maka nilai Salary adalah 12.000.000.

---

### BR-03 - Perhitungan Expense
Untuk kategori bertipe `expense`, nilai laporan dihitung dengan rumus:

```txt
expense_amount = total_debit - total_credit
```

Contoh:
Jika Transport Expense memiliki total debit 200.000 dan total credit 0, maka nilai Transport Expense adalah 200.000.

---

### BR-04 - Total Income
```txt
Total Income = jumlah seluruh nilai kategori bertipe income
```

---

### BR-05 - Total Expense
```txt
Total Expense = jumlah seluruh nilai kategori bertipe expense
```

---

### BR-06 - Net Income
```txt
Net Income = Total Income - Total Expense
```

---

### BR-07 - Validasi Debit/Credit
Dalam satu transaksi, hanya salah satu dari debit atau credit yang boleh lebih dari 0.

Valid:
```txt
debit = 25000, credit = 0
debit = 0, credit = 5000000
```

Tidak valid:
```txt
debit = 25000, credit = 5000000
debit = 0, credit = 0
debit = -10000, credit = 0
```

---

### BR-08 - Nilai Transaksi
- Debit tidak boleh negatif.
- Credit tidak boleh negatif.
- Debit dan credit tidak boleh sama-sama 0.
- Debit dan credit tidak boleh sama-sama lebih dari 0.

---

### BR-09 - Periode Laporan
Laporan Profit/Loss harus ditampilkan berdasarkan filter periode:

```txt
start_month sampai end_month
```

Format bulan yang digunakan di UI:

```txt
YYYY-MM
```

Contoh:
```txt
2022-01 sampai 2022-03
```

## Backend Validation Rules

### Store/Update CoaCategoryRequest
```php
return [
    'name' => ['required', 'string', 'max:255'],
    'type' => ['required', Rule::in(['income', 'expense'])],
];
```

### Store/Update CoaRequest
```php
return [
    'code' => ['required', 'string', 'max:50', Rule::unique('coas', 'code')->ignore($this->coa)],
    'name' => ['required', 'string', 'max:255'],
    'coa_category_id' => ['required', 'exists:coa_categories,id'],
];
```

### Store/Update FinancialTransactionRequest
```php
return [
    'transaction_date' => ['required', 'date'],
    'coa_id' => ['required', 'exists:coas,id'],
    'description' => ['nullable', 'string', 'max:255'],
    'debit' => ['required', 'numeric', 'min:0'],
    'credit' => ['required', 'numeric', 'min:0'],
];
```

Tambahkan custom validation setelah rules dasar:

```php
$debit = (float) $this->input('debit', 0);
$credit = (float) $this->input('credit', 0);

if ($debit <= 0 && $credit <= 0) {
    $validator->errors()->add('debit', 'Debit atau credit harus diisi salah satu.');
}

if ($debit > 0 && $credit > 0) {
    $validator->errors()->add('credit', 'Debit dan credit tidak boleh diisi bersamaan.');
}
```

## Delete Rules
- Kategori COA tidak boleh dihapus jika sudah dipakai oleh COA.
- COA tidak boleh dihapus jika sudah dipakai transaksi.
- Transaksi boleh dihapus, tetapi harus ada konfirmasi di UI.

## Frontend Validation UX
- Tampilkan error langsung di bawah field.
- Untuk debit dan credit, tampilkan helper text: `Isi salah satu: debit atau credit.`
- Jika user mengisi debit lebih dari 0, pertimbangkan auto-set credit menjadi 0.
- Jika user mengisi credit lebih dari 0, pertimbangkan auto-set debit menjadi 0.
