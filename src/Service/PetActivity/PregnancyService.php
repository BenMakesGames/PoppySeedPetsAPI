<?php
namespace App\Service\PetActivity;

use App\Entity\Pet;
use App\Entity\PetBaby;
use App\Entity\PetSkills;
use App\Entity\PetSpecies;
use App\Enum\FlavorEnum;
use App\Enum\LocationEnum;
use App\Functions\ArrayFunctions;
use App\Functions\ColorFunctions;
use App\Functions\NumberFunctions;
use App\Repository\PetRepository;
use App\Repository\PetSpeciesRepository;
use App\Repository\UserQuestRepository;
use App\Service\InventoryService;
use App\Service\PetRelationshipService;
use App\Service\PetService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Location;

class PregnancyService
{
    private $em;
    private $inventoryService;
    private $petRelationshipService;
    private $petRepository;
    private $responseService;
    private $petService;
    private $userQuestRepository;
    private $petSpeciesRepository;

    public function __construct(
        EntityManagerInterface $em, InventoryService $inventoryService, PetRelationshipService $petRelationshipService,
        PetRepository $petRepository, ResponseService $responseService, PetService $petService,
        UserQuestRepository $userQuestRepository, PetSpeciesRepository $petSpeciesRepository
    )
    {
        $this->em = $em;
        $this->inventoryService = $inventoryService;
        $this->petRelationshipService = $petRelationshipService;
        $this->petRepository = $petRepository;
        $this->responseService = $responseService;
        $this->petService = $petService;
        $this->userQuestRepository = $userQuestRepository;
        $this->petSpeciesRepository = $petSpeciesRepository;
    }

    public function getPregnant(Pet $pet1, Pet $pet2)
    {
        if ($pet1->getIsFertile() && !$pet1->getPregnancy())
            $this->createPregnancy($pet1, $pet2);

        if ($pet2->getIsFertile() && !$pet2->getPregnancy())
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
            $species = $this->getRandomBreedingSpecies();

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

    public function getRandomBreedingSpecies(): PetSpecies
    {
        $species = $this->petSpeciesRepository->findBy([ 'availableFromBreeding' => true ]);

        return ArrayFunctions::pick_one($species);
    }

    public function giveBirth(Pet $pet)
    {
        $user = $pet->getOwner();
        $pregnancy = $pet->getPregnancy();

        $baby = (new Pet())
            ->setOwner($user)
            ->setSpecies($pregnancy->getSpecies())
            ->setColorA($pregnancy->getColorA())
            ->setColorB($pregnancy->getColorB())
            ->setMom($pregnancy->getParent())
            ->setDad($pregnancy->getOtherParent())
            ->setName($this->combineNames($pregnancy->getParent()->getName(), $pregnancy->getOtherParent()->getName()))
            ->setFavoriteFlavor(FlavorEnum::getRandomValue())
        ;

        if($pregnancy->getAffection() > 0)
            $pet->increaseAffectionPoints($pet->getAffectionPointsToLevel());

        $babySkills = new PetSkills();

        $this->em->persist($babySkills);

        $baby->setSkills($babySkills);

        $this->petRelationshipService->createParentalRelationships($baby, $pregnancy->getParent(), $pregnancy->getOtherParent());

        $numberOfPetsAtHome = $this->petRepository->getNumberAtHome($user);

        $adjective = ArrayFunctions::pick_one([
            'a beautiful', 'an energetic', 'a wriggly',
            'a smiling', 'an intense-looking', 'a plump',
        ]);

        $increasedPetLimitWithPetBirth = $this->userQuestRepository->findOrCreate($user, 'Increased Pet Limit with Pet Birth', false);

        if(!$increasedPetLimitWithPetBirth->getValue())
        {
            $user->increaseMaxPets(1);
            $increasedPetLimitWithPetBirth->setValue(true);

            $increasedPetLimit = true;
        }
        else
            $increasedPetLimit = false;

        if($numberOfPetsAtHome >= $user->getMaxPets())
        {
            $baby->setInDaycare(true);
            $pet->setInDaycare(true);

            $this->responseService->createActivityLog($pet, $pet->getName() . ' gave birth to ' . $adjective . ' baby ' . $baby->getSpecies()->getName() . '! (There wasn\'t enough room at Home, so the birth took place at the Pet Shelter.)', '');
        }
        else
        {
            if($increasedPetLimit)
                $this->responseService->createActivityLog($pet, $pet->getName() . ' gave birth to ' . $adjective . ' baby ' . $baby->getSpecies()->getName() . '! (Congrats on your first pet birth! The maximum amount of pets you can have at home has been permanently increased by one!)', '');
            else
                $this->responseService->createActivityLog($pet, $pet->getName() . ' gave birth to ' . $adjective . ' baby ' . $baby->getSpecies()->getName() . '!', '');
        }

        $this->inventoryService->receiveItem('Renaming Scroll', $pet->getOwner(), $pet->getOwner(), 'You received this when ' . $baby->getName() . ' was born.', LocationEnum::HOME);

        $pet->setPregnancy(null);

        $this->em->persist($baby);
        $this->em->remove($pregnancy);

        $this->petService->spendTime($pet, mt_rand(45, 75));

        // applied in a slightly weird order, because I-dunno
        $pet
            ->increaseLove(mt_rand(8, 16))
            ->increaseEsteem(mt_rand(8, 16))
            ->increaseSafety(mt_rand(8, 16))
            ->increaseFood(-mt_rand(8, 16))
        ;
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

    public function combineNames(string $n1, string $n2): string
    {
        if(strlen($n1) < 3)
            $n1Part = $n1;
        else
        {
            $n1Offset = mt_rand(
                max(0, ceil(strlen($n1) / 2) - 2),
                min(strlen($n1) - 1, floor(strlen($n1) / 2) + 2)
            );

            if($n1Offset === 0 || $n1Offset === strlen($n1) - 1)
                $n1Part = $n1;
            else if(mt_rand(1, 2) === 1)
                $n1Part = substr($n1, 0, $n1Offset);
            else
                $n1Part = substr($n1, $n1Offset);
        }

        if(strlen($n2) < 3)
            $n2Part = $n2;
        else
        {

            $n2Offset = mt_rand(
                max(0, ceil(strlen($n2) / 2) - 2),
                min(strlen($n2) - 1, floor(strlen($n2) / 2) + 2)
            );

            if($n2Offset === 0 || $n2Offset === strlen($n1) - 1)
                $n2Part = $n2;
            else if(mt_rand(1, 2) === 1)
                $n2Part = substr($n2, 0, $n2Offset);
            else
                $n2Part = substr($n2, $n2Offset);
        }

        if(mt_rand(1, 2) === 1)
            $newName = trim($n1Part . $n2Part);
        else
            $newName = trim($n2Part . $n1Part);

        $newName = preg_replace('/ +/', ' ', strtolower($newName));

        return ucwords($newName);
    }
}