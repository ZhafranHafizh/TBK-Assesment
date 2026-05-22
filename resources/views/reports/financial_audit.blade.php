<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Keuangan Audit</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 12px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
            text-transform: uppercase;
        }
        .header p {
            margin: 5px 0 0 0;
            font-size: 12px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 11px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .font-bold {
            font-weight: bold;
        }
        .summary-table {
            width: 50%;
            margin-left: auto;
        }
        .summary-table th {
            background-color: #e9e9e9;
        }
        .footer {
            margin-top: 30px;
            font-size: 10px;
            color: #999;
            text-align: center;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>Laporan Transaksi Keuangan (Audit Trail)</h1>
        <p style="margin-top: 10px;">Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
        
        <div style="margin: 15px auto 0 auto; padding: 10px; border-top: 1px dashed #ccc; border-bottom: 1px dashed #ccc; text-align: left; font-size: 11px; max-width: 90%; color: #444;">
            <strong style="text-transform: uppercase;">Catatan Sistem Audit:</strong><br>
            Dokumen laporan ini dicetak secara otomatis sebagai bentuk pengarsipan (audit trail) karena adanya tindakan penghapusan data master Kategori COA <strong>"{{ $categoryName }}"</strong> pada tanggal <strong>{{ $deletedDate }}</strong> pukul <strong>{{ $deletedTime }}</strong>.
            Tindakan ini dieksekusi oleh pengguna dengan alamat Email: <strong>{{ $deletedBy }}</strong>.
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center">Tanggal</th>
                <th>Kode Akun</th>
                <th>Nama Akun</th>
                <th>Kategori</th>
                <th>Deskripsi</th>
                <th class="text-right">Debit (Rp)</th>
                <th class="text-right">Credit (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalDebit = 0;
                $totalCredit = 0;
            @endphp

            @forelse($transactions as $tx)
                @php
                    $totalDebit += $tx->debit;
                    $totalCredit += $tx->credit;
                @endphp
                <tr>
                    <td class="text-center">{{ \Carbon\Carbon::parse($tx->transaction_date)->format('d/m/Y') }}</td>
                    <td>{{ $tx->coa->code ?? '-' }}</td>
                    <td>{{ $tx->coa->name ?? '-' }}</td>
                    <td>{{ $tx->coa->coaCategory->name ?? '-' }}</td>
                    <td>{{ $tx->description ?: '-' }}</td>
                    <td class="text-right">{{ number_format($tx->debit, 2, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($tx->credit, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">Tidak ada transaksi pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5" class="text-right">TOTAL KESELURUHAN</th>
                <th class="text-right font-bold">{{ number_format($totalDebit, 2, ',', '.') }}</th>
                <th class="text-right font-bold">{{ number_format($totalCredit, 2, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Dokumen ini dihasilkan secara otomatis oleh Sistem sebagai bagian dari riwayat audit (Audit Trail).
    </div>

</body>
</html>
