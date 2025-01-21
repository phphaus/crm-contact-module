<?php

namespace Example\CrmExample\Http\Requests\Contact;

use Example\CrmExample\Http\Requests\BaseRequest;

class UpdateContactRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'first_name' => ['sometimes', 'string', 'min:2', 'max:255'],
            'last_name' => ['sometimes', 'string', 'min:2', 'max:255'],
            'phones' => ['sometimes', 'array', 'max:10'],
            'phones.*.number' => ['required_with:phones', 'string', 'regex:/^\+(?:61|64)\d{9}$/'],
            'emails' => ['sometimes', 'array', 'max:10'],
            'emails.*.email' => ['required_with:emails', 'email', 'max:255']
        ];
    }

    public function messages(): array
    {
        return [
            'phones.*.number.regex' => 'Phone numbers must be in E.164 format for AU (+61) or NZ (+64)',
            'phones.max' => 'Maximum of 10 phone numbers allowed',
            'emails.max' => 'Maximum of 10 email addresses allowed'
        ];
    }
} 