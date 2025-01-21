<?php

namespace Example\CrmContactModule\Contracts;

use Illuminate\Contracts\Support\Responsable;

interface ApiResponseInterface extends Responsable
{
    /**
     * Create a response from any entity or collection
     * 
     * @param mixed $data Entity or Collection
     */
    public function fromData(mixed $data): self;
} 