<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDistribusiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'matching_id' => [
                'required',
                'exists:matching_barang,id'
            ],

            'driver_id' => [
                'nullable',
                'exists:users,id'
            ],

            'tanggal_pickup' => [
                'required',
                'date'
            ],

            'tanggal_pengiriman' => [
                'required',
                'date',
                'after_or_equal:tanggal_pickup'
            ],

            'jumlah_disalurkan' => [
                'required',
                'integer',
                'min:1'
            ],

            'catatan' => [
                'nullable',
                'string'
            ]
        ];
    }
}