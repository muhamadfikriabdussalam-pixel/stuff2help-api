<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBarangDonasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kategori_id' => 'required|exists:kategori_barang,id',
            'nama_barang' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:5120', // ✅ File gambar
            'foto_url' => 'nullable|url',
            'kondisi' => 'required|in:Baik,Cukup,Perlu Perbaikan',
            'jumlah' => 'required|integer|min:1', // <-- ditambahkan
        ];
    }
}