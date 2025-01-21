<?php

namespace Example\CrmContactModule\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchContactRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'phone' => ['sometimes', 'string', 'regex:/^\+?[1-9]\d{1,14}$/'],
            'email_domain' => ['sometimes', 'string', 'max:255'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
} 