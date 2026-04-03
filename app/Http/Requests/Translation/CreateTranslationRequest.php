<?php

namespace App\Http\Requests\Translation;

use Illuminate\Foundation\Http\FormRequest;

class CreateTranslationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'locale_id' => 'required|exists:locales,id',
            'key' => 'required|string|max:255|unique:translations,key,NULL,id,locale_id,' . $this->locale_id,
            'content' => 'required|string',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
        ];
    }

    public function messages(): array
    {
        return [
            'key.unique' => 'This translation key already exists for the selected locale.',
        ];
    }
}