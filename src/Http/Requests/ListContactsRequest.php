<?php

namespace Example\CrmContactModule\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListContactsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'phone' => ['sometimes', 'string'],
            'email' => ['sometimes', 'string', 'email'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function getPerPage(): int
    {
        return $this->input('per_page', 15);
    }

    public function getPage(): int
    {
        return $this->input('page', 1);
    }
} 