<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDosenRequest extends FormRequest
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
        $dosenId = $this->route('dosen')?->id;

        return [
            'nama' => ['required', 'string', 'max:255'],
            'nip' => ['required', 'string', 'max:30', "unique:dosens,nip,{$dosenId}"],
            'kapasitas_maksimal' => ['nullable', 'integer', 'min:1', 'max:99'],
        ];
    }

    public function messages(): array
    {
        return [
            'nip.unique' => 'NIP sudah digunakan oleh dosen lain.',
        ];
    }
}
