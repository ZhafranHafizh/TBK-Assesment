<table>
    <thead>
        <tr>
            <th colspan="2" style="font-weight: bold; text-align: center; font-size: 14px;">LAPORAN PROFIT/LOSS</th>
        </tr>
        <tr>
            <th colspan="2" style="text-align: center;">Periode: {{ str_pad($data['period']['month'], 2, '0', STR_PAD_LEFT) }} / {{ $data['period']['year'] }}</th>
        </tr>
        <tr>
            <th colspan="2"></th>
        </tr>
        <tr>
            <th style="font-weight: bold; border-bottom: 1px solid #000; width: 30px;">Kategori</th>
            <th style="font-weight: bold; border-bottom: 1px solid #000; text-align: right; width: 25px;">Total (IDR)</th>
        </tr>
    </thead>
    <tbody>
        <!-- Income Section -->
        <tr>
            <td colspan="2" style="font-weight: bold; color: #166534;">PENDAPATAN (INCOME)</td>
        </tr>
        @foreach($data['incomes'] as $income)
        <tr>
            <td>{{ $income['name'] }}</td>
            <td style="text-align: right;">{{ number_format($income['total'], 0, ',', '.') }}</td>
        </tr>
        @endforeach
        <tr>
            <td style="font-weight: bold; text-align: right;">Total Pendapatan</td>
            <td style="font-weight: bold; text-align: right; color: #166534;">{{ number_format($data['total_income'], 0, ',', '.') }}</td>
        </tr>

        <tr>
            <td colspan="2"></td>
        </tr>

        <!-- Expense Section -->
        <tr>
            <td colspan="2" style="font-weight: bold; color: #991b1b;">BEBAN (EXPENSE)</td>
        </tr>
        @foreach($data['expenses'] as $expense)
        <tr>
            <td>{{ $expense['name'] }}</td>
            <td style="text-align: right;">{{ number_format($expense['total'], 0, ',', '.') }}</td>
        </tr>
        @endforeach
        <tr>
            <td style="font-weight: bold; text-align: right;">Total Beban</td>
            <td style="font-weight: bold; text-align: right; color: #991b1b;">{{ number_format($data['total_expense'], 0, ',', '.') }}</td>
        </tr>

        <tr>
            <td colspan="2"></td>
        </tr>

        <!-- Net Income Section -->
        <tr>
            <td style="font-weight: bold; text-align: right;">LABA BERSIH (NET INCOME)</td>
            <td style="font-weight: bold; text-align: right; {{ $data['net_income'] < 0 ? 'color: #991b1b;' : 'color: #166534;' }}">
                {{ number_format($data['net_income'], 0, ',', '.') }}
            </td>
        </tr>
    </tbody>
</table>
