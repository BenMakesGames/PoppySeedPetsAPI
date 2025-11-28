<?php
declare(strict_types = 1);

namespace App\Service\PetActivity;

use App\Entity\PetActivityLog;
use App\Model\ComputedPetSkills;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.petActivity')]
interface IPetActivity
{
    public function preferredWithFullHouse(): bool;
    public function groupKey(): string;
    public function groupDesire(ComputedPetSkills $petWithSkills): int;

    /**
     * @param ComputedPetSkills $petWithSkills
     * @return (callable(ComputedPetSkills): PetActivityLog)[]
     */
    public function possibilities(ComputedPetSkills $petWithSkills): array;
}