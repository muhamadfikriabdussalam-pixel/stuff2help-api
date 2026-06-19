<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'nama' => 'required|string|max:100',

            'username' => 'required|string|max:50|unique:users,username',

            'email' => 'required|email|unique:users,email',

            'password' => 'required|min:8|confirmed',

            'role' => 'required|in:Donatur,Penerima,Driver',

            'no_hp' => 'required|string|max:20',

            'alamat' => 'required|string',

            'kota' => 'required|string|max:100',
        ];
    }
}