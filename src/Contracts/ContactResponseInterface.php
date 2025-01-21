<?php

namespace Example\CrmContactModule\Contracts;

use Example\CrmContactModule\Entities\Contact;
use Illuminate\Contracts\Support\Responsable;

interface ContactResponseInterface extends Responsable
{
    /**
     * Create a response from a Contact entity
     */
    public function fromEntity(Contact $contact): self;
} 