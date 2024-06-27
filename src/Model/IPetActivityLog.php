<?php

namespace App\Model;

use App\Entity\Item;
use App\Entity\PetActivityLogTag;

interface IPetActivityLog
{
    public function getEntry(): string;
    public function setEntry(string $entry): static;
    public function addInterestingness(int $interestingness): static;
    public function addCreatedItem(Item $item): static;

    /**
     * @param PetActivityLogTag[] $tags
     */
    public function addTags(array $tags): static;
}
