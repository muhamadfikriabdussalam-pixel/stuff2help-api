<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRatingDriverRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'distribusi_id' => [
                'required',
                'integer',
                'exists:distribusi,id'
            ],

            'rating' => [
                'required',
                'numeric',
                'min:1',
                'max:5'
            ],

            'ulasan' => [
                'nullable',
                'string'
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'distribusi_id.required' => 'Distribusi wajib dipilih.',
            'distribusi_id.exists' => 'Distribusi tidak ditemukan.',

            'rating.required' => 'Rating wajib diisi.',
            'rating.numeric' => 'Rating harus berupa angka.',
            'rating.min' => 'Rating minimal 1.',
            'rating.max' => 'Rating maksimal 5.'
        ];
    }
}