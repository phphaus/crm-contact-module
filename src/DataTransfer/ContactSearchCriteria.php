<?php

class ContactSearchCriteria
{
    public function __construct(
        private readonly ?string $phone = null,
        private readonly ?string $emailDomain = null,
        private readonly int $page = 1,
        private readonly int $perPage = 15
    ) {
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function getEmailDomain(): ?string
    {
        return $this->emailDomain;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }
} 