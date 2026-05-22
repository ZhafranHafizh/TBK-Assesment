<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCoaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50', 'unique:coas,code'],
            'name' => ['required', 'string', 'max:255'],
            'coa_category_id' => ['required', 'exists:coa_categories,id'],
        ];
    }
}
