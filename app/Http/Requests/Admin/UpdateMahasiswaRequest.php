<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMahasiswaRequest extends FormRequest
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
        // ambil mahasiswa dari route model binding
        $mahasiswaId = $this->route('mahasiswa')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$this->route('mahasiswa')?->user_id],
            'angkatan' => ['required', 'integer', 'digits:4', 'min:2000', 'max:'.(date('Y') + 1)],
            'alamat' => ['nullable', 'string', 'max:500'],
            'password' => ['nullable', 'string', 'min:6'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'Email sudah digunakan oleh akun lain.',
        ];
    }
}
