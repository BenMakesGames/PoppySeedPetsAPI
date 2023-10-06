<?php
namespace App\Service\PetActivity;

use App\Entity\Pet;
use App\Entity\PetBaby;
use App\Entity\PetRelationship;
use App\Entity\PetSpecies;
use App\Enum\FlavorEnum;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetLocationEnum;
use App\Enum\RelationshipEnum;
use App\Enum\UserStatEnum;
use App\Functions\PetActivityLogTagHelpers;
use App\Functions\PetColorFunctions;
use App\Functions\UserStatsHelpers;
use App\Model\PetShelterPet;
use App\Repository\MeritRepository;
use App\Repository\PetRepository;
use App\Repository\UserQuestRepository;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\PetFactory;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;

class PregnancyService
{
    private EntityManagerInterface $em;
    private ResponseService $responseService;
    private PetExperienceService $petExperienceService;
    private UserQuestRepository $userQuestRepository;
    private MeritRepository $meritRepository;
    private PetFactory $petFactory;
    private IRandom $squirrel3;

    public function __construct(
        EntityManagerInterface $em, ResponseService $responseService, PetExperienceService $petExperienceService,
        UserQuestRepository $userQuestRepository, MeritRepository $meritRepository, PetFactory $petFactory,
        IRandom $squirrel3
    )
    {
        $this->em = $em;
        $this->responseService = $responseService;
        $this->petExperienceService = $petExperienceService;
        $this->userQuestRepository = $userQuestRepository;
        $this->meritRepository = $meritRepository;
        $this->petFactory = $petFactory;
        $this->squirrel3 = $squirrel3;
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
        $r = $this->squirrel3->rngNextInt(1, 100);

        if($r <= 45)
            $species = $mother->getSpecies();
        else if($r <= 90)
            $species = $father->getSpecies();
        else
            $species = $this->getRandomBreedingSpecies();

        $colorA = PetColorFunctions::generateColorFromParentColors($this->squirrel3, $mother->getColorA(), $father->getColorA());
        $colorB = PetColorFunctions::generateColorFromParentColors($this->squirrel3, $mother->getColorB(), $father->getColorB());

        // 20% of the time, swap colorA and colorB around
        if($this->squirrel3->rngNextInt(1, 5) === 1)
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

    public function getPregnantViaSpiritCompanion(Pet $mother)
    {
        if(!$mother->getSpiritCompanion())
            throw new \Exception("Spirit companion not found! This is a bug! Ben has been notified...");

        $r = $this->squirrel3->rngNextInt(1, 100);

        if($r <= 80)
            $species = $mother->getSpecies();
        else
            $species = $this->getRandomBreedingSpecies();

        $colorA = PetColorFunctions::generateColorFromParentColors($this->squirrel3, $mother->getColorA(), $mother->getColorA());
        $colorB = PetColorFunctions::generateColorFromParentColors($this->squirrel3, $mother->getColorB(), $mother->getColorB());

        // 20% of the time, swap colorA and colorB around
        if($this->squirrel3->rngNextInt(1, 5) === 1)
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
            ->setSpiritParent($mother->getSpiritCompanion())
        ;

        $this->em->persist($petPregnancy);
    }

    public function getRandomBreedingSpecies(): PetSpecies
    {
        $species = $this->em->getRepository(PetSpecies::class)->findBy([ 'availableFromBreeding' => true ]);

        return $this->squirrel3->rngNextFromArray($species);
    }

    /**
     * @param Pet $pet
     */
    public function giveBirth(Pet $pet)
    {
        $user = $pet->getOwner();
        $pregnancy = $pet->getPregnancy();

        if($pregnancy->getSpiritParent())
        {
            $names = [
                $this->combineNames($pregnancy->getParent()->getName(), $pregnancy->getSpiritParent()->getName()),
                $this->combineNames($pregnancy->getSpiritParent()->getName(), $pregnancy->getParent()->getName())
            ];
        }
        else
        {
            $names = [
                $this->combineNames($pregnancy->getParent()->getName(), $pregnancy->getOtherParent()->getName()),
                $this->combineNames($pregnancy->getOtherParent()->getName(), $pregnancy->getParent()->getName()),
            ];
        }

        shuffle($names);

        $babies = [];

        $babies[] = $this->petFactory->createPet(
            $user,
            $names[0],
            $pregnancy->getSpecies(),
            $pregnancy->getColorA(),
            $pregnancy->getColorB(),
            FlavorEnum::getRandomValue($this->squirrel3),
            $this->meritRepository->getRandomStartingMerit()
        );

        if($pet->hasMerit(MeritEnum::DOPPEL_GENE) || $this->squirrel3->rngNextInt(1, 444) === 1)
        {
            $babies[] = $this->petFactory->createPet(
                $user,
                $names[1],
                $pregnancy->getSpecies(),
                $pregnancy->getColorA(),
                $pregnancy->getColorB(),
                FlavorEnum::getRandomValue($this->squirrel3),
                $this->meritRepository->getRandomStartingMerit()
            );
        }

        if($pregnancy->getSpiritParent())
        {
            foreach($babies as $baby)
                $baby->addMerit(MeritRepository::findOneByName($this->em, MeritEnum::NATURAL_CHANNEL));
        }

        $smallestParent = min($pregnancy->getParent()->getScale(), $pregnancy->getOtherParent() == null ? 50 : $pregnancy->getOtherParent()->getScale());
        $largestParent = max($pregnancy->getParent()->getScale(), $pregnancy->getOtherParent() == null ? 50 : $pregnancy->getOtherParent()->getScale());

        $min = $smallestParent === 80 ? 80 : $this->squirrel3->rngNextInt(min($smallestParent, 80), max($smallestParent, 80));
        $max = $largestParent === 120 ? 120 : $this->squirrel3->rngNextInt(min($largestParent, 120), max($largestParent, 120));

        if($min === $max)
            $babySize = $min;
        else if($min < $max)
            $babySize = $this->squirrel3->rngNextInt($min, $max);
        else
            $babySize = $this->squirrel3->rngNextInt($max, $min);

        foreach($babies as $baby)
        {
            $baby
                ->setMom($pregnancy->getParent())
                ->setDad($pregnancy->getOtherParent())
                ->setSpiritDad($pregnancy->getSpiritParent())
                ->setScale($babySize)
                ->setRenamingCharges(1)
            ;

            if($pregnancy->getAffection() > 0)
                $baby->increaseAffectionPoints($baby->getAffectionPointsToLevel());

            $this->createParentalRelationships($baby, $pregnancy->getParent(), $pregnancy->getOtherParent());
        }

        $numberOfPetsAtHome = PetRepository::getNumberAtHome($this->em, $user);

        $adjective = $this->squirrel3->rngNextFromArray([
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

        $describeBabies = count($babies) > 1
            ? 'two baby ' . $babies[0]->getSpecies()->getName() . 's'
            : $adjective . ' baby ' . $babies[0]->getSpecies()->getName()
        ;

        if($numberOfPetsAtHome + count($babies) > $user->getMaxPets())
        {
            foreach($babies as $baby)
                $baby->setLocation(PetLocationEnum::DAYCARE);

            $pet->setLocation(PetLocationEnum::DAYCARE);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% gave birth to ' . $describeBabies . '! (There wasn\'t enough room at Home, so the birth took place at the Pet Shelter.)', '');
        }
        else
        {
            if($increasedPetLimit)
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% gave birth to ' . $describeBabies . '! (Congrats on your first pet birth! The maximum amount of pets you can have at home has been permanently increased by one!)', '');
            else
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% gave birth to ' . $describeBabies . '!', '');
        }

        $activityLog
            ->addInterestingness(PetActivityLogInterestingnessEnum::GAVE_BIRTH)
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Pregnancy' ]))
        ;

        $pet->setPregnancy(null);

        // grandparents get cool stuff :P
        if($pregnancy->getParent()->getMom()) $pregnancy->getParent()->getMom()->setIsGrandparent(true);
        if($pregnancy->getParent()->getDad()) $pregnancy->getParent()->getDad()->setIsGrandparent(true);

        if($pregnancy->getOtherParent())
        {
            if($pregnancy->getOtherParent()->getMom()) $pregnancy->getOtherParent()->getMom()->setIsGrandparent(true);
            if($pregnancy->getOtherParent()->getDad()) $pregnancy->getOtherParent()->getDad()->setIsGrandparent(true);
        }

        $this->em->remove($pregnancy);

        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::OTHER, null);

        // applied in a slightly weird order, because I-dunno
        $pet
            ->increaseLove($this->squirrel3->rngNextInt(8, 16))
            ->increaseEsteem($this->squirrel3->rngNextInt(8, 16))
            ->increaseSafety($this->squirrel3->rngNextInt(8, 16))
            ->increaseFood(-$this->squirrel3->rngNextInt(8, 16))
        ;

        UserStatsHelpers::incrementStat($this->em, $user, UserStatEnum::PETS_BIRTHED);
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
        'rape',
        'rapist',
        'sket', // skeet
        'spic',
        'wetback',
        'wiger', // wigger
        'wop',
    ];

    public function combineNames(string $n1, string $n2): string
    {
        if(\mb_strlen($n1) < 3)
            $n1Part = $n1;
        else
        {
            $n1Offset = $this->squirrel3->rngNextInt(
                max(0, ceil(\mb_strlen($n1) / 2) - 2),
                min(\mb_strlen($n1) - 1, (\mb_strlen($n1) / 2) + 2)
            );

            if($n1Offset === 0 || $n1Offset === \mb_strlen($n1) - 1)
                $n1Part = $n1;
            else if($this->squirrel3->rngNextBool())
                $n1Part = \mb_substr($n1, 0, $n1Offset);
            else
                $n1Part = \mb_substr($n1, $n1Offset);
        }

        if(\mb_strlen($n2) < 3)
            $n2Part = $n2;
        else
        {
            $n2Offset = $this->squirrel3->rngNextInt(
                max(0, ceil(\mb_strlen($n2) / 2) - 2),
                min(\mb_strlen($n2) - 1, (\mb_strlen($n2) / 2) + 2)
            );

            if($n2Offset === 0 || $n2Offset === \mb_strlen($n1) - 1)
                $n2Part = $n2;
            else if($this->squirrel3->rngNextBool())
                $n2Part = \mb_substr($n2, 0, $n2Offset);
            else
                $n2Part = \mb_substr($n2, $n2Offset);
        }

        $newName = trim($n1Part . $n2Part);

        $newName = preg_replace('/ +/', ' ', mb_strtolower($newName));

        if(PregnancyService::isForbiddenCombinedName($newName))
            $newName = $this->squirrel3->rngNextFromArray(PetShelterPet::PET_NAMES);

        return mb_convert_case($newName, MB_CASE_TITLE);
    }

    private static function isForbiddenCombinedName(string $name): bool
    {
        $canonicalized = mb_strtolower($name);
        $canonicalized = preg_replace('/[^a-z0-9]/', '', $canonicalized); // remove all non-alphanums
        $canonicalized = str_replace([ '0', '1', '2', '3', '4', '5', '7' ], [ 'o', 'i', 'z', 'e', 'a', 's', 't' ], $canonicalized); // l33t
        $canonicalized = preg_replace('/([\s.\'-,])\1+/', '$1', $canonicalized); // remove duplicate characters (ex: "faaaaaaaaaaaag" is as bad as "fag")

        return in_array($canonicalized, self::CANONICALIZED_FORBIDDEN_COMBINED_NAMES);
    }

    /**
     * @param Pet $baby
     * @param Pet $mother
     * @param Pet $father
     */
    private function createParentalRelationships(Pet $baby, Pet $mother, ?Pet $father)
    {
        $petWithMother = (new PetRelationship())
            ->setRelationship($mother)
            ->setCurrentRelationship(RelationshipEnum::BFF)
            ->setPet($baby)
            ->setRelationshipGoal(RelationshipEnum::BFF)
            ->setMetDescription('%relationship.name% gave birth to %pet.name%!')
            ->setCommitment(90) // BFF + BFF
        ;

        $baby->addPetRelationship($petWithMother);

        $this->em->persist($petWithMother);

        if($father)
        {
            $petWithFather = (new PetRelationship())
                ->setRelationship($father)
                ->setCurrentRelationship(RelationshipEnum::BFF)
                ->setPet($baby)
                ->setRelationshipGoal(RelationshipEnum::BFF)
                ->setMetDescription('%relationship.name% fathered %pet.name%!')
                ->setCommitment(90) // BFF + BFF
            ;

            $baby->addPetRelationship($petWithFather);

            $this->em->persist($petWithFather);
        }

        $motherWithBaby = (new PetRelationship())
            ->setRelationship($baby)
            ->setCurrentRelationship(RelationshipEnum::BFF)
            ->setPet($mother)
            ->setRelationshipGoal(RelationshipEnum::BFF)
            ->setMetDescription('%pet.name% gave birth to %relationship.name%!')
            ->setCommitment(90) // BFF + BFF
        ;

        $mother->addPetRelationship($motherWithBaby);

        $this->em->persist($motherWithBaby);

        if($father)
        {
            $fatherWithBaby = (new PetRelationship())
                ->setRelationship($baby)
                ->setCurrentRelationship(RelationshipEnum::BFF)
                ->setPet($father)
                ->setRelationshipGoal(RelationshipEnum::BFF)
                ->setMetDescription('%pet.name% fathered %relationship.name%!')
                ->setCommitment(90) // BFF + BFF
            ;

            $father->addPetRelationship($fatherWithBaby);

            $this->em->persist($fatherWithBaby);
        }
    }
}
