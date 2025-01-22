<?php

namespace Example\CrmContactModule\Http\Requests\Contact;

use Example\CrmContactModule\Http\Requests\BaseRequest;
use Illuminate\Support\Facades\Config;

class StoreContactRequest extends BaseRequest
{
    public function rules(): array
    {
        $maxPhones = Config::get('crm.contacts.limits.phones', 10);
        $maxEmails = Config::get('crm.contacts.limits.emails', 10);

        return [
            'first_name' => ['required', 'string', 'min:2', 'max:255'],
            'last_name' => ['required', 'string', 'min:2', 'max:255'],
            'phones' => ['array', "max:$maxPhones"],
            'phones.*.number' => ['required', 'string', 'regex:/^\+(?:61|64)\d{9}$/'],
            'emails' => ['array', "max:$maxEmails"],
            'emails.*.email' => ['required', 'email', 'max:255']
        ];
    }

    public function messages(): array
    {
        $maxPhones = Config::get('crm.contacts.limits.phones', 10);
        $maxEmails = Config::get('crm.contacts.limits.emails', 10);

        return [
            'phones.*.number.regex' => 'Phone numbers must be in E.164 format for AU (+61) or NZ (+64)',
            'phones.max' => "Maximum of $maxPhones phone numbers allowed",
            'emails.max' => "Maximum of $maxEmails email addresses allowed"
        ];
    }
}
