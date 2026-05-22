# 02 - Database Schema Rules

## Overview
Database menggunakan relational schema:

```txt
coa_categories -> coas -> financial_transactions
```

Kategori COA menentukan apakah transaksi dihitung sebagai `income` atau `expense` pada laporan Profit/Loss.

---

## Table: `coa_categories`

### Fungsi
Menyimpan kategori COA seperti Salary, Other Income, Family Expense, Transport Expense, Meal Expense.

### Fields
| Field | Type | Rule |
|---|---|---|
| id | bigint | primary key |
| name | string | required |
| type | enum | required: `income` atau `expense` |
| created_at | timestamp | Laravel default |
| updated_at | timestamp | Laravel default |

### Migration Notes
```php
$table->id();
$table->string('name');
$table->enum('type', ['income', 'expense']);
$table->timestamps();
```

### Model Relation
```php
public function coas()
{
    return $this->hasMany(Coa::class);
}
```

---

## Table: `coas`

### Fungsi
Menyimpan Master Chart of Account.

### Fields
| Field | Type | Rule |
|---|---|---|
| id | bigint | primary key |
| code | string | required, unique. Contoh: `401` |
| name | string | required. Contoh: `Gaji Karyawan` |
| coa_category_id | foreignId | references `coa_categories.id` |
| created_at | timestamp | Laravel default |
| updated_at | timestamp | Laravel default |

### Migration Notes
```php
$table->id();
$table->string('code')->unique();
$table->string('name');
$table->foreignId('coa_category_id')->constrained('coa_categories')->restrictOnDelete();
$table->timestamps();
```

### Model Relation
```php
public function category()
{
    return $this->belongsTo(CoaCategory::class, 'coa_category_id');
}

public function transactions()
{
    return $this->hasMany(FinancialTransaction::class);
}
```

---

## Table: `financial_transactions`

### Fungsi
Menyimpan transaksi debit/credit berdasarkan COA.

### Fields
| Field | Type | Rule |
|---|---|---|
| id | bigint | primary key |
| transaction_date | date | required |
| coa_id | foreignId | references `coas.id` |
| description | string/text | nullable atau required sesuai kebutuhan form |
| debit | decimal(15,2) | default 0, tidak boleh negatif |
| credit | decimal(15,2) | default 0, tidak boleh negatif |
| created_at | timestamp | Laravel default |
| updated_at | timestamp | Laravel default |

### Migration Notes
```php
$table->id();
$table->date('transaction_date');
$table->foreignId('coa_id')->constrained('coas')->restrictOnDelete();
$table->string('description')->nullable();
$table->decimal('debit', 15, 2)->default(0);
$table->decimal('credit', 15, 2)->default(0);
$table->timestamps();
```

### Model Relation
```php
public function coa()
{
    return $this->belongsTo(Coa::class);
}
```

---

## Seeder Data Awal
Buat seeder agar reviewer bisa langsung mencoba aplikasi tanpa input manual.

### Kategori COA
| Name | Type |
|---|---|
| Salary | income |
| Other Income | income |
| Family Expense | expense |
| Transport Expense | expense |
| Meal Expense | expense |

### Master COA
| Code | Name | Category |
|---|---|---|
| 401 | Gaji Karyawan | Salary |
| 402 | Gaji Ketua MPR | Salary |
| 403 | Profit Trading | Other Income |
| 601 | Biaya Sekolah | Family Expense |
| 602 | Bensin | Transport Expense |
| 603 | Parkir | Transport Expense |
| 604 | Makan Siang | Meal Expense |
| 605 | Makan Pokok Bulanan | Meal Expense |

### Contoh Transaksi
Gunakan transaksi contoh dari soal. Income dimasukkan melalui credit, expense dimasukkan melalui debit.

Contoh:
| Date | COA Code | Description | Debit | Credit |
|---|---|---|---:|---:|
| 2022-01-01 | 401 | Gaji Di Perusahaan A | 0 | 5000000 |
| 2022-01-02 | 402 | Gaji Ketua | 0 | 7000000 |
| 2022-01-10 | 602 | Bensin Anak | 25000 | 0 |

## Indexing Recommendation
Tambahkan index untuk field yang sering difilter:
- `financial_transactions.transaction_date`
- `financial_transactions.coa_id`
- `coas.coa_category_id`
