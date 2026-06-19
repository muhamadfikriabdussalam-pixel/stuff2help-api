<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBarangDonasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kategori_id' => 'sometimes|exists:kategori_barang,id',
            'nama_barang' => 'sometimes|string|max:255',
            'deskripsi' => 'nullable|string',
            'foto_url' => 'nullable|url',
            'kondisi' => 'sometimes|in:Baik,Cukup,Perlu Perbaikan',
            'status' => 'sometimes|in:Menunggu Pencocokkan,Tercocokkan,Penjemputan,Pengiriman,Selesai,Dibatalkan',
        ];
    }
}