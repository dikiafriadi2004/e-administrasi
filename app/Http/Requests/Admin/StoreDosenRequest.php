<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreDosenRequest extends FormRequest
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
            'nama' => ['required', 'string', 'max:255'],
            'nip' => ['required', 'string', 'max:30', 'unique:dosens,nip'],
            'kapasitas_maksimal' => ['nullable', 'integer', 'min:1', 'max:99'],
            'bidang_kajian' => ['nullable', 'array'],
            'bidang_kajian.*' => ['string', 'max:100'],
            'mata_kuliah' => ['nullable', 'array'],
            'mata_kuliah.*' => ['string', 'max:150'],
        ];
    }

    public function messages(): array
    {
        return [
            'nip.unique' => 'NIP sudah terdaftar.',
        ];
    }

    /**
     * Bersihkan array kosong sebelum validasi.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'bidang_kajian' => array_values(array_filter($this->bidang_kajian ?? [])),
            'mata_kuliah' => array_values(array_filter($this->mata_kuliah ?? [])),
        ]);
    }
}
