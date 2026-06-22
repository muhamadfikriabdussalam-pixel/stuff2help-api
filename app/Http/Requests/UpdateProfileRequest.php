<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama' => 'sometimes|string|max:100',
            'username' => 'sometimes|string|max:50|unique:users,username,' . $this->user()->id,
            'email' => 'sometimes|email|unique:users,email,' . $this->user()->id,
            'no_hp' => 'nullable|string|max:20',
            'alamat' => 'nullable|string|max:255',
            'kota' => 'nullable|string|max:100',
            'foto_profil' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ];
    }
}