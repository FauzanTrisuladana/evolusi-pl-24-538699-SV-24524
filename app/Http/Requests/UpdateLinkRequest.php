<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class UpdateLinkRequest extends FormRequest
{
    /**
     * The key to be used for the view error bag.
     *
     * @var string
     */
    protected $errorBag = 'edit';

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
        $id = $this->route('id');

        return [
            'target_url' => 'required|string|max:2048',
            'custom_alias' => 'required|alpha_dash|unique:links,new_link,'.$id.',id_link',
            'name' => 'required|string|max:255',
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
            'target_url.required' => 'URL tujuan wajib diisi.',
            'target_url.string' => 'Format URL tujuan tidak valid.',
            'custom_alias.required' => 'Alias wajib diisi.',
            'custom_alias.alpha_dash' => 'Alias khusus hanya boleh berisi huruf, angka, strip (-), dan garis bawah (_).',
            'custom_alias.unique' => 'Alias khusus sudah digunakan. Silakan pilih yang lain.',
            'name.required' => 'Nama link wajib diisi.',
            'name.string' => 'Nama link harus berupa teks.',
            'name.max' => 'Nama link maksimal 255 karakter.',
            'is_active.boolean' => 'Status link tidak valid.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        session()->flash('id', $this->route('id'));
        parent::failedValidation($validator);
    }
}
