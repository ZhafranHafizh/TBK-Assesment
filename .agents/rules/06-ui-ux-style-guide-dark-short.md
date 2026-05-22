# 06 - UI/UX Style Guide

## Tujuan Visual

Gunakan tampilan **dark mode first** yang simple, elegan, dan profesional. Inspirasi utama tetap dari GitHub, tetapi arah visualnya mengikuti **GitHub Dark / GitHub Dark Dimmed**, bukan light mode.

Aplikasi ini adalah web app pencatatan keuangan dan laporan Profit/Loss, jadi UI harus fokus pada kejelasan data, kemudahan input, dan kenyamanan membaca tabel.

## Prinsip Utama

- Dark mode first.
- Simple, clean, dan tidak ramai.
- UX smooth, tapi tidak berlebihan.
- Fokus pada CRUD, tabel, laporan, dan form.
- Jangan membuat UI terasa seperti landing page crypto/futuristic.
- Jangan gunakan neon glow, glassmorphism, atau gradient berat.
- Gunakan border halus, spacing rapi, dan kontras yang nyaman.
- Semua halaman harus terasa konsisten sebagai satu aplikasi admin.

## Color Palette

Gunakan warna dark yang dekat dengan GitHub Dark.

```txt
App Background: #0d1117
Surface/Card: #161b22
Surface Hover: #1f2937
Border: #30363d
Border Subtle: #21262d

Text Primary: #e6edf3
Text Secondary: #8b949e
Text Muted: #6e7681

Primary Green: #238636
Primary Green Hover: #2ea043

Blue Link/Info: #58a6ff
Danger: #f85149
Warning: #d29922

Income Soft BG: rgba(35, 134, 54, 0.15)
Income Text: #3fb950

Expense Soft BG: rgba(248, 81, 73, 0.14)
Expense Text: #ff7b72
```

## Typography

Gunakan font system default agar terasa native dan profesional.

```css
font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Helvetica, Arial, sans-serif;
```

Rules:
- Heading tidak perlu terlalu besar.
- Gunakan font-weight secukupnya.
- Prioritaskan readability.
- Angka nominal sebaiknya mudah discan dan rata kanan di tabel/laporan.

## Layout

Gunakan layout admin app, bukan landing page.

Struktur:

```txt
App Shell
├── Top Navigation
│   ├── App Name
│   ├── Menu
│   └── Optional Action Area
│
└── Main Content
    ├── Page Header
    ├── Stats / Filter / Actions
    └── Table / Form / Report
```

Gunakan container:

```txt
mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6
```

Background utama:

```txt
min-h-screen bg-[#0d1117] text-[#e6edf3]
```

## Navigation

Navigation harus simple seperti GitHub:

- Header dark.
- Border bawah tipis.
- App name di kiri.
- Menu horizontal atau sidebar sederhana.
- Active menu diberi underline, border, atau background subtle.
- Jangan gunakan tombol neon/glow.

Menu minimal:

- Dashboard
- Kategori COA
- Chart of Account
- Transaksi
- Profit/Loss Report

## Component Rules

### Card

Gunakan card sederhana:

```txt
rounded-md border border-[#30363d] bg-[#161b22] p-4
```

Rules:
- Shadow boleh sangat subtle atau tidak perlu.
- Jangan pakai glow.
- Jangan pakai gradient berat.
- Card harus membantu grouping informasi, bukan jadi dekorasi.

### Button

Primary button:

```txt
rounded-md bg-[#238636] px-3 py-2 text-sm font-medium text-white hover:bg-[#2ea043]
```

Secondary button:

```txt
rounded-md border border-[#30363d] bg-[#21262d] px-3 py-2 text-sm text-[#e6edf3] hover:bg-[#30363d]
```

Danger button:

```txt
rounded-md border border-[#f85149]/40 bg-transparent px-3 py-2 text-sm text-[#ff7b72] hover:bg-[#f85149]/10
```

Button UX:
- Hover harus terasa halus.
- Gunakan transition singkat: `transition-colors duration-150`.
- Jangan pakai animasi bounce/glow berlebihan.
- Loading state harus jelas jika ada proses submit/export.

### Table

Tabel adalah komponen penting. Buat rapi dan mudah dibaca.

Rules:
- Wrapper border rounded.
- Header background sedikit lebih gelap/kontras.
- Row border tipis.
- Hover row subtle.
- Nilai angka rata kanan.
- Empty state jelas.
- Action button tidak terlalu besar.

Contoh:

```txt
overflow-hidden rounded-md border border-[#30363d] bg-[#161b22]
thead bg-[#21262d]
tbody divide-y divide-[#30363d]
tr hover:bg-[#1f2937]
```

Untuk Profit/Loss:
- Income row gunakan warna hijau soft.
- Expense row gunakan warna merah soft.
- Total Income dan Total Expense lebih bold.
- Net Income diberi border-top dan font bold.
- Jangan gunakan warna terlalu terang sampai menyakitkan mata.

### Form

Form harus simpel dan nyaman.

Rules:
- Label jelas.
- Input dark surface.
- Border halus.
- Focus state terlihat.
- Error message merah jelas.
- Layout jangan terlalu padat.

Input style:

```txt
rounded-md border border-[#30363d] bg-[#0d1117] px-3 py-2 text-[#e6edf3]
placeholder:text-[#6e7681]
focus:border-[#58a6ff] focus:ring-1 focus:ring-[#58a6ff]
```

### Badge

Income badge:

```txt
bg-[#238636]/15 text-[#3fb950] border border-[#238636]/30
```

Expense badge:

```txt
bg-[#f85149]/15 text-[#ff7b72] border border-[#f85149]/30
```

Badge harus kecil, clean, dan tidak neon.

## Dashboard

Dashboard tidak boleh berbentuk hero landing page.

Gunakan struktur:
- Page title: `Dashboard`
- Subtitle singkat.
- 3 stat cards:
  - Total Income
  - Total Expense
  - Net Income
- 1 chart card untuk tren Net Income.
- Optional: transaksi terbaru.

Copy contoh:

```txt
Dashboard
Ringkasan performa keuangan berdasarkan transaksi yang tercatat.
```

## Page Copy

Gunakan bahasa sederhana dan profesional.

Contoh:

```txt
Kategori COA
Kelola kategori akun berdasarkan tipe income atau expense.
```

```txt
Chart of Account
Kelola daftar akun yang digunakan untuk mencatat transaksi.
```

```txt
Transaksi
Catat pemasukan dan pengeluaran berdasarkan COA.
```

```txt
Laporan Profit/Loss
Lihat ringkasan laba rugi bulanan berdasarkan kategori akun.
```

Hindari copy marketing seperti:
- “Kelola Keuangan Secara Presisi”
- “Financial Engine”
- “Live Simulator Demo”

## Smooth UX Rules

UX harus terasa smooth, tapi tetap ringan.

Gunakan:
- `transition-colors duration-150`
- `transition-all duration-150` hanya jika perlu.
- Hover subtle pada button, row, card clickable.
- Loading state pada submit/export.
- Disabled state yang jelas.
- Toast/feedback sederhana setelah create/update/delete/export.
- Empty state untuk tabel kosong.
- Confirmation dialog untuk delete.
- Form error harus muncul dekat field terkait.

Jangan gunakan:
- Animasi berlebihan.
- Parallax.
- Glow animation.
- Modal entrance terlalu dramatis.
- Efek 3D atau motion yang mengganggu fokus.

## Responsive Rules

- Desktop: gunakan layout max-width dan tabel penuh.
- Mobile: navigation boleh stack atau scroll horizontal.
- Tabel boleh horizontal scroll jika kolom banyak.
- Form menjadi 1 kolom di mobile.
- Button action di mobile harus tetap mudah ditekan.

## Implementation Notes

Fokus ubah styling, bukan business logic.

Jangan ubah:
- route
- CRUD logic
- validasi debit/credit
- query laporan
- export Excel
- migration/model/service

Boleh ubah:
- layout Vue
- Tailwind class
- komponen button/card/table/form
- navigation
- page header
- empty/loading/error state

## Tailwind Quick Examples

App shell:

```txt
min-h-screen bg-[#0d1117] text-[#e6edf3]
```

Header:

```txt
border-b border-[#30363d] bg-[#161b22]
```

Container:

```txt
mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6
```

Card:

```txt
rounded-md border border-[#30363d] bg-[#161b22] p-4
```

Primary button:

```txt
rounded-md bg-[#238636] px-3 py-2 text-sm font-medium text-white transition-colors duration-150 hover:bg-[#2ea043]
```

Secondary button:

```txt
rounded-md border border-[#30363d] bg-[#21262d] px-3 py-2 text-sm text-[#e6edf3] transition-colors duration-150 hover:bg-[#30363d]
```

Table wrapper:

```txt
overflow-hidden rounded-md border border-[#30363d] bg-[#161b22]
```

## Final Direction

Hasil akhir harus terasa seperti:

```txt
GitHub Dark admin dashboard
+ finance app clarity
+ smooth but minimal UX
```

Bukan:

```txt
crypto dashboard
neon landing page
glassmorphism app
overdesigned SaaS template
```
