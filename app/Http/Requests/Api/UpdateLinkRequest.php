<?php

namespace App\Http\Requests\Api;

use App\Models\Link;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateLinkRequest extends FormRequest
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
        $routeParam = $this->route('link') ?? $this->route('id');
        $id = $routeParam instanceof Link ? $routeParam->id_link : $routeParam;

        return [
            'name' => 'required|string|max:255',
            'target_url' => 'required|string|max:2048',
            'custom_alias' => 'required|alpha_dash|unique:links,new_link,'.$id.',id_link|max:255',
            'is_active' => 'nullable|boolean',
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
            'name.required' => 'Nama link wajib diisi.',
            'name.string' => 'Nama link harus berupa teks.',
            'name.max' => 'Nama link maksimal 255 karakter.',
            'target_url.required' => 'URL tujuan wajib diisi.',
            'target_url.string' => 'Format URL tujuan tidak valid.',
            'custom_alias.required' => 'Alias wajib diisi.',
            'custom_alias.alpha_dash' => 'Alias khusus hanya boleh berisi huruf, angka, strip (-), dan garis bawah (_).',
            'custom_alias.unique' => 'Alias khusus sudah digunakan. Silakan pilih yang lain.',
            'is_active.boolean' => 'Status link tidak valid.',
        ];
    }
}
