<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDistribusiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'driver_id' => [
                'nullable',
                'exists:users,id'
            ],

            'tanggal_pickup' => [
                'sometimes',
                'date'
            ],

            'tanggal_pengiriman' => [
                'sometimes',
                'date'
            ],

            'jumlah_disalurkan' => [
                'sometimes',
                'integer',
                'min:1'
            ],

            'status' => [
                'sometimes',
                'in:Menunggu Driver,Driver Ditugaskan,Dalam Penjemputan,Dalam Pengiriman,Menunggu Verifikasi,Selesai,Dibatalkan'
            ],

            'catatan' => [
                'nullable',
                'string'
            ]
        ];
    }
}