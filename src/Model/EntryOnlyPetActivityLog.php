<?php

namespace App\Model;

use App\Entity\Item;

class EntryOnlyPetActivityLog implements IPetActivityLog
{
    private string $entry;

    public function __construct(string $entry)
    {
        $this->entry = $entry;
    }

    public function getEntry(): string
    {
        return $this->entry;
    }

    public function setEntry(string $entry): static
    {
        $this->entry = $entry;
        return $this;
    }

    public function addInterestingness(int $interestingness): static
    {
        // do nothing
        return $this;
    }

    public function addCreatedItem(Item $item): static
    {
        // do nothing
        return $this;
    }

    public function addTags(array $tags): static
    {
        // do nothing
        return $this;
    }
}