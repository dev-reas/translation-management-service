<?php

namespace App\Http\Requests\Locale;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLocaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $localeId = $this->route('locale');
        
        return [
            'code' => 'sometimes|string|max:10|unique:locales,code,' . $localeId . '|regex:/^[a-z]{2}$/',
            'name' => 'sometimes|string|max:100',
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