<?php

namespace Example\CrmContactModule\Contracts;

use Doctrine\Common\Collections\Collection;
use Illuminate\Contracts\Support\Responsable;

interface ContactCollectionResponseInterface extends Responsable
{
    /**
     * Create a response from a collection of Contact entities
     * 
     * @param Collection<int, Contact> $contacts
     */
    public function fromCollection(Collection $contacts): self;
} 