<?php

namespace App\Http\Requests\Tag;

use Illuminate\Foundation\Http\FormRequest;

class CreateTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100|unique:tags,name|regex:/^[a-z0-9-]+$/',
            'description' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'name.regex' => 'The name must contain only lowercase letters, numbers, and hyphens (e.g., mobile, desktop, web).',
        ];
    }
}