<?php

namespace App\Http\Requests\Locale;

use Illuminate\Foundation\Http\FormRequest;

class CreateLocaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:10|unique:locales,code|regex:/^[a-z]{2}$/',
            'name' => 'required|string|max:100',
            'is_active' => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'code.regex' => 'The code must be a 2-letter lowercase language code (e.g., en, fr, es).',
        ];
    }
}