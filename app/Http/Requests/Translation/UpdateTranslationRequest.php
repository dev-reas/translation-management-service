<?php

namespace App\Http\Requests\Translation;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTranslationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $translationId = $this->route('translation');
        
        return [
            'locale_id' => 'sometimes|exists:locales,id',
            'key' => 'sometimes|string|max:255|unique:translations,key,' . $translationId . ',id,locale_id,' . $this->locale_id,
            'content' => 'sometimes|string',
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