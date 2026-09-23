<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ShortenLinkRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'target_url' => 'required|string|max:2048',
            'custom_alias' => 'nullable|alpha_dash|unique:links,new_link|max:255',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'target_url.required' => 'URL tujuan wajib diisi.',
            'target_url.string' => 'Format URL tujuan tidak valid.',
            'custom_alias.alpha_dash' => 'Alias khusus hanya boleh berisi huruf, angka, strip (-), dan garis bawah (_).',
            'custom_alias.unique' => 'Alias khusus sudah digunakan. Silakan pilih yang lain.',
        ];
    }
}
