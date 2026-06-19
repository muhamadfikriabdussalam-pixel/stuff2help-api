<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTrackingDriverRequest extends FormRequest
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
                'exists:distribusi,id'
            ],

            'latitude' => [
                'required',
                'numeric',
                'between:-90,90'
            ],

            'longitude' => [
                'required',
                'numeric',
                'between:-180,180'
            ],

            'waktu_lokasi' => [
                'required',
                'date'
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'distribusi_id.required' =>
                'Distribusi wajib dipilih.',

            'distribusi_id.exists' =>
                'Distribusi tidak ditemukan.',

            'latitude.required' =>
                'Latitude wajib diisi.',

            'latitude.between' =>
                'Latitude harus berada di antara -90 sampai 90.',

            'longitude.required' =>
                'Longitude wajib diisi.',

            'longitude.between' =>
                'Longitude harus berada di antara -180 sampai 180.',

            'waktu_lokasi.required' =>
                'Waktu lokasi wajib diisi.',

            'waktu_lokasi.date' =>
                'Format waktu lokasi tidak valid.'
        ];
    }
}