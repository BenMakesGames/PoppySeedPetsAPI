<?php
namespace App\Model;

use Symfony\Component\Serializer\Annotation\Groups;

class TotalPetSkill
{
    #[Groups(['myPet'])]
    public int $base = 0;

    #[Groups(['myPet'])]
    public int $merits = 0;

    #[Groups(['myPet'])]
    public int $tool = 0;

    #[Groups(['myPet'])]
    public int $statusEffects = 0;

    public function getTotal(): int
    {
        return $this->base + $this->merits + $this->tool + $this->statusEffects;
    }
}
