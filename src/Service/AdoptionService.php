<?php
namespace App\Service;

use App\Entity\User;
use App\Enum\UserStatEnum;
use App\Functions\ColorFunctions;
use App\Functions\DateFunctions;
use App\Model\ChineseCalendarInfo;
use App\Model\PetShelterPet;
use App\Repository\PetRepository;
use App\Repository\PetSpeciesRepository;
use App\Repository\UserStatsRepository;

class AdoptionService
{
    private PetRepository $petRepository;
    private PetSpeciesRepository $petSpeciesRepository;
    private CalendarService $calendarService;
    private UserStatsRepository $userStatsRepository;
    private ChineseCalendarInfo $chineseCalendarInfo;

    public function __construct(
        PetRepository $petRepository, PetSpeciesRepository $petSpeciesRepository, CalendarService $calendarService,
        UserStatsRepository $userStatsRepository
    )
    {
        $this->petRepository = $petRepository;
        $this->petSpeciesRepository = $petSpeciesRepository;
        $this->calendarService = $calendarService;
        $this->userStatsRepository = $userStatsRepository;

        $this->chineseCalendarInfo = $calendarService->getChineseCalendarInfo();
    }

    public function getPetsAdopted(User $user): int
    {
        return $this->userStatsRepository->getStatValue($user, UserStatEnum::PETS_ADOPTED);
    }

    public function getAdoptionFee(User $user): int
    {
        $fee = 100;

        $petsAdopted = $this->getPetsAdopted($user);

        if($petsAdopted == 0)
            $fee = ceil($fee / 2);

        if($this->calendarService->deprecatedIsBlackFriday() || $this->calendarService->deprecatedIsCyberMonday())
            $fee = ceil($fee / 10) * 5;

        return $fee;
    }

    private function getNumberOfPets(User $user, Squirrel3 $squirrel3): int
    {
        $bonus = $this->getPetsAdopted($user) > 0 && $squirrel3->rngNextInt(1, 31) === 1 ? 10 : 0;

        return $squirrel3->rngNextInt(4, 8) + $bonus;
    }

    public function getDailyPets(User $user): array
    {
        $now = new \DateTimeImmutable();
        $nowString = $now->format('Y-m-d');

        $squirrel3 = new Squirrel3();
        $squirrel3->setSeed($user->getDailySeed());

        $numPets = $this->getNumberOfPets($user, $squirrel3);
        $numSeasonalPets = $this->numberOfSeasonalPets($numPets, $squirrel3);
        $petsAdopted = $this->getPetsAdopted($user);

        if($petsAdopted == 0)
            $dialog = "Hello! Here to adopt a new friend? Your first pet is 50% off!\n\n If";
        else
        {
            $dialog = $numPets > 10
                ? "Oh, goodness! A bunch of pets appeared from the Portal today! It just seems to happen now and again; we're still not sure why...\n\n Anyway, if "
                : "Hello! Here to adopt a new friend?\n\nIf "
            ;
        }

        $petCount = $this->petRepository->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->andWhere('p.birthDate<:today')
            ->setParameter('today', $nowString)
            ->getQuery()
            ->getSingleScalarResult()
        ;

        $fullMoonName = DateFunctions::getFullMoonName($now);
        $isBlueMoon = $fullMoonName === 'Blue';
        $isPinkMoon = $fullMoonName === 'Pink';
        $pets = [];

        $allSpecies = $this->petSpeciesRepository->findBy([ 'availableFromPetShelter' => true ]);

        for($i = 0; $i < $numPets; $i++)
        {
            if($i < $numSeasonalPets)
            {
                [$colorA, $colorB] = $squirrel3->rngNextSubsetFromArray($this->getSeasonalColors(), 2);

                $seasonalNames = $this->getSeasonalNames();

                if(count($seasonalNames) === $numSeasonalPets)
                    $name = $seasonalNames[$i];
                else
                    $name = $squirrel3->rngNextFromArray($seasonalNames);
            }
            else if($i === $numPets - 1 && !$isBlueMoon && !$isPinkMoon)
            {
                // RANDOM!
                $h1 = $squirrel3->rngNextInt(0, 1000) / 1000.0;
                $s1 = $squirrel3->rngNextInt($squirrel3->rngNextInt(0, 500), 1000) / 1000.0;
                $l1 = $squirrel3->rngNextInt($squirrel3->rngNextInt(0, 500), $squirrel3->rngNextInt(750, 1000)) / 1000.0;

                $h2 = $squirrel3->rngNextInt(0, 1000) / 1000.0;
                $s2 = $squirrel3->rngNextInt($squirrel3->rngNextInt(0, 500), 1000) / 1000.0;
                $l2 = $squirrel3->rngNextInt($squirrel3->rngNextInt(0, 500), $squirrel3->rngNextInt(750, 1000)) / 1000.0;

                $colorA = ColorFunctions::HSL2Hex($h1, $s1, $l1);
                $colorB = ColorFunctions::HSL2Hex($h2, $s2, $l2);

                $name = $squirrel3->rngNextFromArray(PetShelterPet::PET_NAMES);
            }
            else
            {
                if($isBlueMoon)
                {
                    $blueA = $squirrel3->rngNextInt(127, 255);
                    $otherA = $squirrel3->rngNextInt(0, $blueA - 16);

                    $blueB = $squirrel3->rngNextInt(127, 255);
                    $otherB = $squirrel3->rngNextInt(0, $blueB - 16);

                    $colorA = ColorFunctions::RGB2Hex($otherA, $otherA, $blueA);
                    $colorB = ColorFunctions::RGB2Hex($otherB, $otherB, $blueB);

                    $colorA = $squirrel3->rngNextTweakedColor($colorA);
                    $colorB = $squirrel3->rngNextTweakedColor($colorB);
                }
                else if($isPinkMoon)
                {
                    $redA = $squirrel3->rngNextInt(224, 255);
                    $otherA = $squirrel3->rngNextInt(128, $redA - 32);

                    $redB = $squirrel3->rngNextInt(224, 255);
                    $otherB = $squirrel3->rngNextInt(128, $redB - 32);

                    $colorA = ColorFunctions::RGB2Hex($redA, $otherA, $otherA);
                    $colorB = ColorFunctions::RGB2Hex($redB, $otherB, $otherB);

                    $colorA = $squirrel3->rngNextTweakedColor($colorA);
                    $colorB = $squirrel3->rngNextTweakedColor($colorB);
                }
                else
                {
                    $basePet = $this->petRepository->createQueryBuilder('p')
                        ->andWhere('p.birthDate<:today')
                        ->setParameter('today', $nowString)
                        ->setMaxResults(1)
                        ->setFirstResult($squirrel3->rngNextInt(0, $petCount - 1))
                        ->getQuery()
                        ->getSingleResult();

                    $colorA = $squirrel3->rngNextTweakedColor($basePet->getColorA());
                    $colorB = $squirrel3->rngNextTweakedColor($basePet->getColorB());
                }

                if($this->calendarService->deprecatedIsPiDay())
                    $name = $squirrel3->rngNextFromArray([ 'Pi',  'Pi', 'Pie', 'Pie', 'Pie', 'Pie', 'Pie', 'Cake' ]);
                else
                    $name = $squirrel3->rngNextFromArray(PetShelterPet::PET_NAMES);
            }

            $pet = new PetShelterPet();

            if($this->calendarService->deprecatedIsHalloweenDay())
            {
                $pet->species = $this->petSpeciesRepository->findOneBy([ 'name' => 'Fog Elemental' ]);
                $pet->label = 'spooky!';
                $dialog = "Uh... I don't know if this is a Halloween thing, or what, but... if you want a Fog Elemental, I guess it's your pick of the litter...\n\nAlthough I guess if ";
            }
            else if($this->calendarService->deprecatedIsNoombatDay())
            {
                $pet->species = $this->petSpeciesRepository->findOneBy([ 'name' => 'Noombat' ]);
                $pet->label = 'noom!';
                $dialog = "Agh! This happens every year at about this time! Noombats everywhere! I don't know if it's Noombat breeding season, or what, but please adopt one of these things! If you insist, though, and ";
            }
            else if($squirrel3->rngNextInt(1, 200) === 1)
            {
                $pet->species = $squirrel3->rngNextFromArray($this->petSpeciesRepository->findBy([ 'availableFromPetShelter' => false, 'availableFromBreeding' => true ]));
                $pet->label = $squirrel3->rngNextFromArray([
                    'gasp!',
                    'oh my!',
                    'whoa!',
                    'ooooh!',
                ]);
            }
            else
                $pet->species = $squirrel3->rngNextFromArray($allSpecies);

            $pet->name = $name;
            $pet->colorA = $colorA;
            $pet->colorB = $colorB;
            $pet->id = $squirrel3->rngNextInt(100000, 999999) * 10 + $i;
            $pet->scale = $squirrel3->rngNextInt(80, 120);

            $pets[] = $pet;
        }

        return [ $pets, $dialog ];
    }

    public function numberOfSeasonalPets(int $totalPets, Squirrel3 $squirrel3): int
    {
        $monthDay = $this->calendarService->getMonthAndDay();

        if($this->calendarService->deprecatedIsHalloween())
            return $squirrel3->rngNextInt(1, 2);

        // PSP Thanksgiving overlaps Black Friday, but for pet adoption purposes, we want Black Friday to win out:
        if($this->calendarService->deprecatedIsBlackFriday() || $this->calendarService->deprecatedIsCyberMonday())
            return ceil($totalPets / 2);

        if($this->calendarService->deprecatedIsThanksgiving())
            return $squirrel3->rngNextInt(1, 2);

        if($this->calendarService->deprecatedIsEaster())
            return $squirrel3->rngNextInt(1, 2);

        if($this->calendarService->deprecatedIsValentinesOrAdjacent() || $this->calendarService->deprecatedIsWhiteDay())
            return 2;

        // winter solstice, more or less
        if($monthDay === 1221 || $monthDay === 1222)
            return ceil($totalPets / 2);

        // Christmas colors
        if($monthDay >= 1223 && $monthDay <= 1225)
            return $squirrel3->rngNextInt(1, 2);

        if($this->calendarService->deprecatedIsHanukkah())
            return $squirrel3->rngNextInt(1, 2);

        if($this->chineseCalendarInfo->month === 1 && $this->chineseCalendarInfo->day <= 6)
            return 2;

        if($this->calendarService->deprecatedIsSaintPatricksDay())
            return $squirrel3->rngNextInt(1, 3);

        return 0;
    }

    public function getSeasonalNames(): array
    {
        $monthDay = $this->calendarService->getMonthAndDay();

        if($this->calendarService->deprecatedIsHalloween())
            return PetShelterPet::PET_HALLOWEEN_NAMES;

        // PSP Thanksgiving overlaps Black Friday, but for pet adoption purposes, we want Black Friday to win out:
        if($this->calendarService->deprecatedIsBlackFriday())
            return PetShelterPet::PET_BLACK_FRIDAY_NAMES;

        if($this->calendarService->deprecatedIsCyberMonday())
            return PetShelterPet::PET_CYBER_MONDAY_NAMES;

        if($this->calendarService->deprecatedIsThanksgiving())
            return PetShelterPet::PET_THANKSGIVING_NAMES;

        if($this->calendarService->deprecatedIsEaster())
            return PetShelterPet::PET_EASTER_NAMES;

        if($this->calendarService->deprecatedIsValentinesOrAdjacent())
            return PetShelterPet::PET_VALENTINES_NAMES;

        if($this->calendarService->deprecatedIsWhiteDay())
            return PetShelterPet::PET_WHITE_DAY_NAMES;

        // winter solstice, more or less
        if($this->calendarService->deprecatedIsWinterSolstice())
            return PetShelterPet::PET_WINTER_SOLSTICE_NAMES;

        // Christmas colors (would normally do a 3-day range, but dec 23 isWinterSolstice())
        if($monthDay >= 1224 && $monthDay <= 1225)
            return PetShelterPet::PET_CHRISTMAS_NAMES;

        if($this->calendarService->deprecatedIsHanukkah())
            return PetShelterPet::PET_HANUKKAH_NAMES;

        if($this->chineseCalendarInfo->month === 1 && $this->chineseCalendarInfo->day <= 6)
            return PetShelterPet::PET_CHINESE_ZODIAC_NAMES[$this->chineseCalendarInfo->animal];

        if($this->calendarService->deprecatedIsSaintPatricksDay())
            return PetShelterPet::PET_NAMES;

        throw new \Exception('Today is not a day for seasonal colors.');
    }

    public function getSeasonalColors(): array
    {
        $monthDay = $this->calendarService->getMonthAndDay();

        if($this->calendarService->deprecatedIsHalloween())
            return [ '333333', 'FF9933' ];

        // PSP Thanksgiving overlaps Black Friday, but for pet adoption purposes, we want Black Friday to win out:
        if($this->calendarService->deprecatedIsBlackFriday())
            return [ '000000', '333333', '330000', '003300', '000033' ];

        if($this->calendarService->deprecatedIsCyberMonday())
            return [ '000000', '005500', '00aa00', '00ff00' ];

        if($this->calendarService->deprecatedIsThanksgiving())
            return [ 'CC6600', 'FFCC00', '009900', 'FF3300' ];

        if($this->calendarService->deprecatedIsEaster())
            return [ 'FFCCFF', '99CCFF', 'FFFF99', 'FF9999' ];

        if($this->calendarService->deprecatedIsValentinesOrAdjacent())
            return [ 'F17B7B', 'F8F8F8', 'FF0000', 'EF85FF' ];

        if($this->calendarService->deprecatedIsWhiteDay())
            return [ 'FFFFFF', 'EEEEEE' ];

        // winter solstice, more or less
        if($monthDay === 1221 || $monthDay === 1222)
            return [ 'F8F8F8', '94C6F8' ];

        // Christmas colors
        if($monthDay >= 1223 && $monthDay <= 1225)
            return [ 'F8F8F8', 'CC3300', '009900' ];

        if($this->calendarService->deprecatedIsHanukkah())
            return [ 'F8F8F8', '0066FF' ];

        if($this->chineseCalendarInfo->month === 1 && $this->chineseCalendarInfo->day <= 6)
            return [ 'CC232A', 'F5AC27', 'FFD84B', 'F2888B', 'A3262A', 'CC9902' ];

        if($this->calendarService->deprecatedIsSaintPatricksDay())
            return [ '009900', '66CC66', '33AA00', '00AA33' ];

        throw new \Exception('Today is not a day for seasonal colors.');
    }
}
