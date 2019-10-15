<?php
namespace App\Service;

use App\Entity\Pet;
use App\Entity\PetBaby;
use App\Functions\ColorFunctions;
use App\Functions\NumberFunctions;
use Doctrine\ORM\EntityManagerInterface;

class PregnancyService
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function getPregnant(Pet $pet1, Pet $pet2)
    {
        if (!$pet1->getPregnancy())
            $this->createPregnancy($pet1, $pet2);

        if (!$pet2->getPregnancy())
            $this->createPregnancy($pet2, $pet1);
    }

    private function createPregnancy(Pet $mother, Pet $father)
    {
        $r = mt_rand(1, 100);
        if($r <= 45)
            $species = $mother->getSpecies();
        else if($r <= 90)
            $species = $father->getSpecies();
        else
            $species = null;

        $colorA = $this->generateColor($mother->getColorA(), $father->getColorA());
        $colorB = $this->generateColor($mother->getColorB(), $father->getColorB());

        // 20% of the time, swap colorA and colorB around
        if(mt_rand(1, 5) === 1)
        {
            $temp = $colorA;
            $colorA = $colorB;
            $colorB = $temp;
        }

        $petPregnancy = (new PetBaby())
            ->setSpecies($species)
            ->setColorA($colorA)
            ->setColorB($colorB)
            ->setParent($mother)
            ->setOtherParent($father)
        ;

        $this->em->persist($petPregnancy);
    }

    private function generateColor(string $color1, string $color2): string
    {
        if(mt_rand(1, 5) === 1)
        {
            return ColorFunctions::HSL2Hex(mt_rand(0, 100) / 100, mt_rand(0, 100) / 100, mt_rand(0, 100) / 100);
        }
        else
        {
            // pick a color somewhere between color1 and color2, tending to prefer a 50/50 mix
            $skew = mt_rand(mt_rand(0, 50), mt_rand(50, 100));

            $rgb1 = ColorFunctions::Hex2RGB($color1);
            $rgb2 = ColorFunctions::Hex2RGB($color2);

            $r = (int)(($rgb1['r'] * $skew + $rgb2['r'] * (100 - $skew)) / 100);
            $g = (int)(($rgb1['g'] * $skew + $rgb2['g'] * (100 - $skew)) / 100);
            $b = (int)(($rgb1['b'] * $skew + $rgb2['b'] * (100 - $skew)) / 100);

            // jiggle the final values a little:
            $r = NumberFunctions::constrain($r + mt_rand(-6, 6), 0, 255);
            $g = NumberFunctions::constrain($g + mt_rand(-6, 6), 0, 255);
            $b = NumberFunctions::constrain($b + mt_rand(-6, 6), 0, 255);

            return ColorFunctions::RGB2Hex($r, $g, $b);
        }
    }
}