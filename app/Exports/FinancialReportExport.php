<?php

namespace App\Exports;

use App\Models\FinancialTransaction;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class FinancialReportExport implements FromQuery, WithHeadings, WithMapping
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function query()
    {
        return FinancialTransaction::query()
            ->with(['coa.coaCategory'])
            ->whereBetween('transaction_date', [$this->startDate, $this->endDate])
            ->orderBy('transaction_date');
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Kode Akun',
            'Nama Akun',
            'Kategori',
            'Deskripsi',
            'Debit',
            'Credit'
        ];
    }

    public function map($transaction): array
    {
        return [
            $transaction->transaction_date,
            $transaction->coa->code ?? '-',
            $transaction->coa->name ?? '-',
            $transaction->coa->coaCategory->name ?? '-',
            $transaction->description,
            $transaction->debit,
            $transaction->credit,
        ];
    }
}
