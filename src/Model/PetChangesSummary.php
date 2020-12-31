<?php
namespace App\Model;

use Symfony\Component\Serializer\Annotation\Groups;

class PetChangesSummary
{
    /**
     * @var string|null
     * @Groups({"petActivityLogs", "petActivityLogAndPublicPet"})
     */
    public $food;

    /**
     * @var string|null
     * @Groups({"petActivityLogs", "petActivityLogAndPublicPet"})
     */
    public $safety;

    /**
     * @var int
     * @Groups({"petActivityLogs", "petActivityLogAndPublicPet"})
     */
    public $love;

    /**
     * @var string|null
     * @Groups({"petActivityLogs", "petActivityLogAndPublicPet"})
     */
    public $esteem;

    /**
     * @var string|null
     * @Groups({"petActivityLogs", "petActivityLogAndPublicPet"})
     */
    public $exp;

    /**
     * @var string|null
     * @Groups({"petActivityLogs", "petActivityLogAndPublicPet"})
     */
    public $level;

    /**
     * @var string|null
     * @Groups({"petActivityLogs", "petActivityLogAndPublicPet"})
     */
    public $affection;

    /**
     * @var string|null
     * @Groups({"petActivityLogs", "petActivityLogAndPublicPet"})
     */
    public $affectionLevel;

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
