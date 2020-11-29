<?php
namespace App\Model;

use Symfony\Component\Serializer\Annotation\Groups;

class TotalPetSkill
{
    /**
     * @Groups({"myPet"})
     * @var int
     */
    public $base = 0;

    /**
     * @Groups({"myPet"})
     * @var int
     */
    public $merits = 0;

    /**
     * @Groups({"myPet"})
     * @var int
     */
    public $tool = 0;

    /**
     * @Groups({"myPet"})
     * @var int
     */
    public $statusEffects = 0;

    public function getTotal(): int
    {
        return $this->base + $this->merits + $this->tool + $this->statusEffects;
    }
}
