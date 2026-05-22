# 05 - Report, Export Excel & Dashboard Rules

## M3 - Laporan Profit/Loss

### Tujuan
Menampilkan laporan laba rugi bulanan berbentuk pivot/cross-tabulation.

### Struktur Tabel
Baris:
- Kategori COA
- Total Income
- Total Expense
- Net Income

Kolom:
- Bulan-Tahun dalam format `YYYY-MM`

Contoh:
| Category | 2022-01 | 2022-02 | 2022-03 |
|---|---:|---:|---:|
| Salary | 12000000 | 12000000 | 1200000 |
| Other Income | 5500000 | 6000000 | 350000 |
| Total Income | 17500000 | 18000000 | 1550000 |
| Family Expense | 500000 | 350000 | 450000 |
| Transport Expense | 20000 | 25000 | 22500 |
| Meal Expense | 150000 | 30000 | 175000 |
| Total Expense | 850000 | 405000 | 490000 |
| Net Income | 16650000 | 13950000 | 1060000 |

### Filter Periode
Wajib ada filter:
- Start month
- End month

Format input:
```txt
YYYY-MM
```

Default value bisa menggunakan:
- Bulan awal dari data transaksi pertama
- Bulan akhir dari data transaksi terakhir

Atau default ke tahun berjalan jika data masih kosong.

### Calculation Rules
Untuk setiap kategori per bulan:

Income:
```txt
SUM(credit) - SUM(debit)
```

Expense:
```txt
SUM(debit) - SUM(credit)
```

Total Income:
```txt
SUM(all income categories)
```

Total Expense:
```txt
SUM(all expense categories)
```

Net Income:
```txt
Total Income - Total Expense
```

### Service Class
Gunakan `ProfitLossReportService`.

Output service disarankan berbentuk array seperti:

```php
[
    'months' => ['2022-01', '2022-02', '2022-03'],
    'incomeRows' => [
        ['category' => 'Salary', 'values' => ['2022-01' => 12000000]],
    ],
    'expenseRows' => [
        ['category' => 'Transport Expense', 'values' => ['2022-01' => 20000]],
    ],
    'totals' => [
        'income' => ['2022-01' => 17500000],
        'expense' => ['2022-01' => 850000],
        'netIncome' => ['2022-01' => 16650000],
    ],
]
```

## M4 - Export Excel

### Requirement
Tombol export harus mengunduh laporan Profit/Loss berdasarkan filter yang sedang aktif.

Jika user sedang melihat:
```txt
2022-01 sampai 2022-03
```

maka file Excel juga harus berisi periode tersebut saja.

### Library
Gunakan `maatwebsite/excel`.

### Nama File
Gunakan format nama file:
```txt
profit-loss-report-YYYY-MM-to-YYYY-MM.xlsx
```

Contoh:
```txt
profit-loss-report-2022-01-to-2022-03.xlsx
```

### Format Excel Minimal
- Header laporan
- Periode laporan
- Tabel kategori vs bulan
- Total Income
- Total Expense
- Net Income

### Format Styling Excel Optional
Jika sempat:
- Bold untuk header dan total rows
- Border table
- Number format ribuan
- Freeze top row
- Auto size columns

## M5 - Dashboard Analytics

### Tujuan
Memberi ringkasan cepat kondisi keuangan.

### Cards Minimal
Tampilkan 3 cards:
- Total Income bulan ini
- Total Expense bulan ini
- Net Income bulan ini

Optional card:
- Jumlah transaksi bulan ini

### Chart Minimal
Tampilkan satu grafik tren Net Income per bulan.

Gunakan Chart.js atau ApexCharts.

### Dashboard Rule
Dashboard hanya fitur nilai tambah. Jangan menghabiskan terlalu banyak waktu di sini jika fitur CRUD, report, dan export belum selesai.

### Empty State Dashboard
Jika belum ada data transaksi:
```txt
Belum ada data transaksi. Tambahkan transaksi untuk melihat dashboard analytics.
```
