<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreMahasiswaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'nim' => ['required', 'string', 'max:20', 'unique:mahasiswas,nim'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'angkatan' => ['required', 'integer', 'digits:4', 'min:2000', 'max:'.(date('Y') + 1)],
            'alamat' => ['nullable', 'string', 'max:500'],
            'password' => ['nullable', 'string', 'min:6'],
        ];
    }

    public function messages(): array
    {
        return [
            'nim.unique' => 'NIM sudah terdaftar.',
            'email.unique' => 'Email sudah terdaftar.',
            'angkatan.digits' => 'Angkatan harus 4 digit tahun.',
        ];
    }
}
