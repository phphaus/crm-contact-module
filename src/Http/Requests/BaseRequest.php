<?php

namespace Example\CrmContactModule\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class BaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization is handled by middleware
        return true;
    }
}
