<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFinancialTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'transaction_date' => ['required', 'date'],
            'coa_id' => ['required', 'exists:coas,id'],
            'description' => ['nullable', 'string', 'max:255'],
            'debit' => ['required', 'numeric', 'min:0'],
            'credit' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $debit = (float) $this->input('debit', 0);
            $credit = (float) $this->input('credit', 0);

            if ($debit == 0 && $credit == 0) {
                $validator->errors()->add('debit', 'Transaksi harus memiliki setidaknya satu nilai lebih dari 0 pada Debit atau Credit.');
                $validator->errors()->add('credit', 'Transaksi harus memiliki setidaknya satu nilai lebih dari 0 pada Debit atau Credit.');
            }
        });
    }
}
