---
trigger: always_on
---

# 00 - Project Overview

## Nama Project
Web App Pencatatan Keuangan & Laporan Profit/Loss

## Tujuan Utama
Bangun aplikasi web untuk mencatat transaksi keuangan berbasis Chart of Account (COA), mengelompokkan COA berdasarkan kategori `income` atau `expense`, lalu menghasilkan laporan Profit/Loss bulanan secara otomatis.

Aplikasi harus mendukung:
- CRUD Master Kategori COA
- CRUD Master Chart of Account (COA)
- CRUD Transaksi Keuangan
- Laporan Profit/Loss berbentuk pivot/cross-tab bulanan
- Filter periode laporan
- Export laporan ke Excel `.xlsx`
- Dashboard analytics sederhana sebagai nilai tambah

## Target Pengguna
Admin atau user pencatat keuangan sederhana yang ingin mencatat debit/credit dan melihat rekap Profit/Loss per bulan.

## Tech Stack
- Backend: Laravel 13
- Frontend: Vue.js 3
- Bridge: Inertia.js
- Styling: Tailwind CSS
- Database: MySQL
- Export Excel: maatwebsite/excel
- Chart: Chart.js atau ApexCharts

## Scope Utama
Prioritas utama project adalah membuat CRUD, transaksi, laporan Profit/Loss, dan export Excel berjalan stabil. Dashboard hanya fitur nilai tambah, tidak boleh mengganggu fitur inti.

## Modul Sistem
| Kode | Modul | Deskripsi |
|---|---|---|
| M1 | Master Data | CRUD kategori COA dan CRUD COA |
| M2 | Transaksi Keuangan | CRUD transaksi debit/credit berdasarkan COA |
| M3 | Laporan Profit/Loss | Laporan bulanan berbasis kategori COA |
| M4 | Export Excel | Export laporan Profit/Loss sesuai filter |
| M5 | Dashboard Analytics | Ringkasan income, expense, net income, dan grafik tren |

## Prinsip Implementasi
- Gunakan pendekatan modular.
- Jangan menumpuk logic laporan di controller.
- Gunakan service class untuk report calculation.
- Validasi wajib dilakukan di backend menggunakan Laravel Form Request.
- Frontend boleh punya validasi tambahan, tetapi backend tetap sumber validasi utama.
- Gunakan relasi database dan foreign key agar data konsisten.
