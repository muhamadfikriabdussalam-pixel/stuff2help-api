<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVoucherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'voucher_id' => [
                'required',
                'integer',
                'exists:voucher,id'
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'voucher_id.required' => 'Voucher wajib dipilih.',
            'voucher_id.integer' => 'ID voucher tidak valid.',
            'voucher_id.exists' => 'Voucher tidak ditemukan.'
        ];
    }
}