# 01 - Tech Stack & Architecture Rules

## Stack Final
Project menggunakan:
- Laravel 11
- Vue.js 3
- Inertia.js
- Tailwind CSS
- MySQL
- maatwebsite/excel untuk export Excel
- Chart.js atau ApexCharts untuk dashboard chart

## Arsitektur Umum
Gunakan Laravel sebagai backend utama dan Vue 3 sebagai frontend melalui Inertia.js.

Struktur logic disarankan:
- Controller: menerima request, memanggil service, mengembalikan Inertia response atau redirect.
- Form Request: validasi input.
- Model: relasi Eloquent.
- Service: business logic yang cukup kompleks, terutama laporan Profit/Loss.
- Export Class: logic export Excel.
- Seeder: data awal untuk demo.

## Struktur Folder Backend yang Disarankan
```txt
app/
  Http/
    Controllers/
      CoaCategoryController.php
      CoaController.php
      FinancialTransactionController.php
      ProfitLossReportController.php
      DashboardController.php
    Requests/
      StoreCoaCategoryRequest.php
      UpdateCoaCategoryRequest.php
      StoreCoaRequest.php
      UpdateCoaRequest.php
      StoreFinancialTransactionRequest.php
      UpdateFinancialTransactionRequest.php
  Models/
    CoaCategory.php
    Coa.php
    FinancialTransaction.php
  Services/
    ProfitLossReportService.php
    DashboardService.php
  Exports/
    ProfitLossExport.php
```

## Struktur Folder Frontend yang Disarankan
```txt
resources/js/
  Pages/
    Dashboard/Index.vue
    CoaCategories/Index.vue
    CoaCategories/Create.vue
    CoaCategories/Edit.vue
    Coas/Index.vue
    Coas/Create.vue
    Coas/Edit.vue
    Transactions/Index.vue
    Transactions/Create.vue
    Transactions/Edit.vue
    Reports/ProfitLoss.vue
  Components/
    AppLayout.vue
    PageHeader.vue
    DataTable.vue
    EmptyState.vue
    FormError.vue
    ConfirmDeleteModal.vue
    StatCard.vue
    ReportFilter.vue
    ProfitLossTable.vue
```

## Routing
Gunakan resource route untuk CRUD:
```php
Route::resource('coa-categories', CoaCategoryController::class);
Route::resource('coas', CoaController::class);
Route::resource('transactions', FinancialTransactionController::class);
```

Route report:
```php
Route::get('/reports/profit-loss', [ProfitLossReportController::class, 'index'])->name('reports.profit-loss');
Route::get('/reports/profit-loss/export', [ProfitLossReportController::class, 'export'])->name('reports.profit-loss.export');
```

Route dashboard:
```php
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
```

## Naming Rules
Gunakan Bahasa Inggris untuk nama tabel, field, class, method, dan route agar konsisten dengan convention Laravel.

Contoh:
- Gunakan `code`, bukan `kode`.
- Gunakan `financial_transactions`, bukan `transaksi_keuangan`.
- Gunakan `transaction_date`, bukan `tanggal_transaksi`.

## Service Layer Rule
Semua logic perhitungan Profit/Loss harus diletakkan di `ProfitLossReportService`, bukan langsung di controller.

Controller hanya boleh:
1. Membaca filter request.
2. Memanggil service.
3. Mengembalikan data ke Vue/Inertia.
4. Menjalankan export jika dibutuhkan.

## Data Integrity
- Semua relasi wajib menggunakan foreign key.
- Data kategori yang sudah dipakai COA tidak boleh dihapus.
- Data COA yang sudah dipakai transaksi tidak boleh dihapus.
- Gunakan database transaction jika ada proses yang mengubah beberapa tabel sekaligus.
