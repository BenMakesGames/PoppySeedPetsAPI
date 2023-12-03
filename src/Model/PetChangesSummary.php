<?php
namespace App\Model;

use Symfony\Component\Serializer\Annotation\Groups;

class PetChangesSummary
{
    /**
     * @Groups({"petActivityLogs", "petActivityLogAndPublicPet"})
     */
    public string|null $food = null;

    /**
     * @Groups({"petActivityLogs", "petActivityLogAndPublicPet"})
     */
    public string|null $safety = null;

    /**
     * @Groups({"petActivityLogs", "petActivityLogAndPublicPet"})
     */
    public string|null $love = null;

    /**
     * @Groups({"petActivityLogs", "petActivityLogAndPublicPet"})
     */
    public string|null $esteem = null;

    /**
     * @Groups({"petActivityLogs", "petActivityLogAndPublicPet"})
     */
    public string|null $exp = null;

    /**
     * @Groups({"petActivityLogs", "petActivityLogAndPublicPet"})
     */
    public string|null $level = null;

    /**
     * @Groups({"petActivityLogs", "petActivityLogAndPublicPet"})
     */
    public string|null $affection = null;

    /**
     * @Groups({"petActivityLogs", "petActivityLogAndPublicPet"})
     */
    public string|null $affectionLevel = null;

    /**
     * @Groups({"petActivityLogs", "petActivityLogAndPublicPet"})
     */
    public string|null $scrollLevel = null;

    public function containsLevelUp(): bool
    {
        return $this->level !== null && str_contains($this->level, '+');
    }

    public static function rate($value): ?string
    {
        if($value > 20)
            return '++++';
        else if($value > 10)
            return '+++';
        else if($value > 4)
            return '++';
        else if($value > 0)
            return '+';
        else if($value < -20)
            return '----';
        else if($value < -10)
            return '---';
        else if($value < -4)
            return '--';
        else if($value < 0)
            return '-';
        else
            return null;
    }
}
