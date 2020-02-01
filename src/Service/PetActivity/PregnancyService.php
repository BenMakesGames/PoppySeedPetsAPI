<?php
namespace App\Service\PetActivity;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\PetBaby;
use App\Entity\PetSkills;
use App\Entity\PetSpecies;
use App\Enum\EnumInvalidValueException;
use App\Enum\FlavorEnum;
use App\Enum\LocationEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\UserStatEnum;
use App\Functions\ArrayFunctions;
use App\Functions\ColorFunctions;
use App\Functions\NumberFunctions;
use App\Model\PetShelterPet;
use App\Repository\MeritRepository;
use App\Repository\PetRepository;
use App\Repository\PetSpeciesRepository;
use App\Repository\UserQuestRepository;
use App\Repository\UserStatsRepository;
use App\Service\InventoryService;
use App\Service\PetExperienceService;
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
    private $petExperienceService;
    private $userQuestRepository;
    private $petSpeciesRepository;
    private $userStatsRepository;
    private $meritRepository;

    public function __construct(
        EntityManagerInterface $em, InventoryService $inventoryService, PetRelationshipService $petRelationshipService,
        PetRepository $petRepository, ResponseService $responseService, PetExperienceService $petExperienceService,
        UserQuestRepository $userQuestRepository, PetSpeciesRepository $petSpeciesRepository,
        UserStatsRepository $userStatsRepository, MeritRepository $meritRepository
    )
    {
        $this->em = $em;
        $this->inventoryService = $inventoryService;
        $this->petRelationshipService = $petRelationshipService;
        $this->petRepository = $petRepository;
        $this->responseService = $responseService;
        $this->petExperienceService = $petExperienceService;
        $this->userQuestRepository = $userQuestRepository;
        $this->petSpeciesRepository = $petSpeciesRepository;
        $this->userStatsRepository = $userStatsRepository;
        $this->meritRepository = $meritRepository;
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

    /**
     * @param Pet $pet
     * @throws EnumInvalidValueException
     */
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
            ->addMerit($this->meritRepository->getRandomStartingMerit())
        ;

        if($pregnancy->getAffection() > 0)
            $baby->increaseAffectionPoints($baby->getAffectionPointsToLevel());

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

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' gave birth to ' . $adjective . ' baby ' . $baby->getSpecies()->getName() . '! (There wasn\'t enough room at Home, so the birth took place at the Pet Shelter.)', '');
        }
        else
        {
            if($increasedPetLimit)
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' gave birth to ' . $adjective . ' baby ' . $baby->getSpecies()->getName() . '! (Congrats on your first pet birth! The maximum amount of pets you can have at home has been permanently increased by one!)', '');
            else
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' gave birth to ' . $adjective . ' baby ' . $baby->getSpecies()->getName() . '!', '');
        }

        $activityLog->addInterestingness(PetActivityLogInterestingnessEnum::GAVE_BIRTH);

        $this->inventoryService->receiveItem('Renaming Scroll', $pet->getOwner(), $pet->getOwner(), 'You received this when ' . $baby->getName() . ' was born.', LocationEnum::HOME);

        $pet->setPregnancy(null);

        $this->em->persist($baby);
        $this->em->remove($pregnancy);

        $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::OTHER, null);

        // applied in a slightly weird order, because I-dunno
        $pet
            ->increaseLove(mt_rand(8, 16))
            ->increaseEsteem(mt_rand(8, 16))
            ->increaseSafety(mt_rand(8, 16))
            ->increaseFood(-mt_rand(8, 16))
        ;

        $this->userStatsRepository->incrementStat($user, UserStatEnum::PETS_BIRTHED);
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

    private const CANONICALIZED_FORBIDDEN_COMBINED_NAMES = [
        'beaner',
        'chink',
        'con', // coon
        'cunt',
        'dago',
        'dyke',
        'fag',
        'fagot', // faggot
        'gok', // gook
        'heb', // heeb
        'kike',
        'niger', // nigger
        'prick',
        'sket', // skeet
        'spic',
        'wetback',
        'wiger', // wigger
        'wop',
    ];

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

        if($this->isForbiddenCombinedName($newName))
            $newName = ArrayFunctions::pick_one(PetShelterPet::PET_NAMES);

        return ucwords($newName);
    }

    private function isForbiddenCombinedName(string $name)
    {
        $canonicalized = strtolower($name);
        $canonicalized = preg_replace('/[^a-z0-9]/', '', $canonicalized); // remove all non-alphanums
        $canonicalized = str_replace([ '0', '1', '2', '3', '4', '5', '7' ], [ 'o', 'i', 'z', 'e', 'a', 's', 't' ], $canonicalized); // l33t
        $canonicalized = preg_replace('/([\s.\'-,])\1+/', '$1', $canonicalized); // remove duplicate characters (ex: "faaaaaaaaaaaag" is as bad as "fag")

        return in_array($canonicalized, self::CANONICALIZED_FORBIDDEN_COMBINED_NAMES);
    }
}
