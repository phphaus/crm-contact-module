<?php

namespace Example\CrmContactModule\Contracts;

use Example\CrmContactModule\Entities\ContactCall;
use Illuminate\Contracts\Support\Responsable;

interface CallResponseInterface extends Responsable
{
    public function fromEntity(ContactCall $call): self;
} 