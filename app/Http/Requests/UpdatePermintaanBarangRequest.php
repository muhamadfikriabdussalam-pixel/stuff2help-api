<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePermintaanBarangRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kategori_id' => 'sometimes|exists:kategori_barang,id',

            'judul_permintaan' => 'sometimes|string|max:150',

            'jumlah' => 'sometimes|integer|min:1',

            'deskripsi' => 'nullable|string',

            'prioritas' => 'sometimes|in:Rendah,Sedang,Tinggi',

            'status' => 'sometimes|in:Aktif,Terpenuhi,Dibatalkan',
        ];
    }
}