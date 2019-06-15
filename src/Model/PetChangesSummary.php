<?php
namespace App\Model;

use Symfony\Component\Serializer\Annotation\Groups;

class PetChangesSummary
{
    /**
     * @var string|null
     * @Groups({"myPets"})
     */
    public $food;

    /**
     * @var string|null
     * @Groups({"myPets"})
     */
    public $safety;

    /**
     * @var int
     * @Groups({"myPets"})
     */
    public $love;

    /**
     * @var string|null
     * @Groups({"myPets"})
     */
    public $esteem;

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