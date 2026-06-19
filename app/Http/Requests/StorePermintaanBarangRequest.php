<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePermintaanBarangRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kategori_id' => 'required|exists:kategori_barang,id',

            'judul_permintaan' => 'required|string|max:150',

            'jumlah' => 'required|integer|min:1',

            'deskripsi' => 'nullable|string',

            'prioritas' => 'required|in:Rendah,Sedang,Tinggi',
        ];
    }
}