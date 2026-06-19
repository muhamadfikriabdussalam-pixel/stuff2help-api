<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBuktiPengirimanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'distribusi_id' => 'required|integer|exists:distribusi,id',
            'foto_bukti' => 'required|image|mimes:jpg,jpeg,png|max:5120',
            'nama_penerima' => 'required|string|max:100',
            'catatan' => 'nullable|string',
            'waktu_serah_terima' => 'required|date'
        ];
    }
}