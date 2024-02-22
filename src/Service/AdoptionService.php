<?php
namespace App\Service;

use App\Entity\Pet;
use App\Entity\PetSpecies;
use App\Entity\User;
use App\Enum\UserStatEnum;
use App\Functions\CalendarFunctions;
use App\Functions\ColorFunctions;
use App\Functions\DateFunctions;
use App\Functions\RandomFunctions;
use App\Model\ChineseCalendarInfo;
use App\Model\PetShelterPet;
use Doctrine\ORM\EntityManagerInterface;

class AdoptionService
{
    private ChineseCalendarInfo $chineseCalendarInfo;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserStatsService $userStatsRepository,
        private readonly Clock $clock
    )
    {
        $this->chineseCalendarInfo = CalendarFunctions::getChineseCalendarInfo($this->clock->now);
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

        if(CalendarFunctions::isBlackFriday($this->clock->now) || CalendarFunctions::isCyberMonday($this->clock->now))
            $fee = ceil($fee / 10) * 5;

        return $fee;
    }

    public static function getNumberOfPets(\DateTimeImmutable $dt): int
    {
        $year = (int)$dt->format('Y');
        $monthAndDay = (int)$dt->format('nd');

        $bonus = (RandomFunctions::squirrel3Noise($year, $monthAndDay) & 31) === 1 ? 10 : 0;

        $extra = RandomFunctions::squirrel3Noise($year - 100, $monthAndDay) % 5;

        return 4 + $extra + $bonus;
    }

    public function getDailyPets(User $user): array
    {
        $nowString = $this->clock->now->format('Y-m-d');

        $squirrel3 = new Squirrel3($user->getDailySeed());

        $numPets = self::getNumberOfPets($this->clock->now);
        $numSeasonalPets = $this->numberOfSeasonalPets($numPets, $squirrel3);
        $petsAdopted = $this->getPetsAdopted($user);

        if($petsAdopted == 0)
            $dialog = "Hello! Here to adopt a new friend? Your first pet is 50% off!\n\nIf ";
        else
        {
            $dialog = $numPets > 10
                ? "Oh, goodness! A bunch of pets appeared from the Hollow Earth today! It just seems to happen now and again; we're still not sure why...\n\n Anyway, if "
                : "Hello! Here to adopt a new friend?\n\nIf "
            ;
        }

        $petCount = $this->em->getRepository(Pet::class)->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->andWhere('p.birthDate<:today')
            ->setParameter('today', $nowString)
            ->getQuery()
            ->getSingleScalarResult()
        ;

        $fullMoonName = DateFunctions::getFullMoonName($this->clock->now);
        $isBlueMoon = $fullMoonName === 'Blue';
        $isPinkMoon = $fullMoonName === 'Pink';
        $pets = [];

        $allSpecies = $this->em->getRepository(PetSpecies::class)->findBy([ 'availableFromPetShelter' => true ]);

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
                    $basePet = $this->em->getRepository(Pet::class)->createQueryBuilder('p')
                        ->andWhere('p.birthDate<:today')
                        ->setParameter('today', $nowString)
                        ->setMaxResults(1)
                        ->setFirstResult($squirrel3->rngNextInt(0, $petCount - 1))
                        ->getQuery()
                        ->getSingleResult();

                    $colorA = $squirrel3->rngNextTweakedColor($basePet->getColorA());
                    $colorB = $squirrel3->rngNextTweakedColor($basePet->getColorB());
                }

                if(CalendarFunctions::isPiDay($this->clock->now))
                    $name = $squirrel3->rngNextFromArray([ 'Pi',  'Pi', 'Pie', 'Pie', 'Pie', 'Pie', 'Pie', 'Cake' ]);
                else
                    $name = $squirrel3->rngNextFromArray(PetShelterPet::PET_NAMES);
            }

            $pet = new PetShelterPet();

            if(CalendarFunctions::isHalloweenDay($this->clock->now))
            {
                $pet->species = $this->em->getRepository(PetSpecies::class)->findOneBy([ 'name' => 'Fog Elemental' ]);
                $pet->label = 'spooky!';
                $dialog = "Uh... I don't know if this is a Halloween thing, or what, but... if you want a Fog Elemental, I guess it's your pick of the litter...\n\nAlthough I guess if ";
            }
            else if(CalendarFunctions::isNoombatDay($this->clock->now))
            {
                $pet->species = $this->em->getRepository(PetSpecies::class)->findOneBy([ 'name' => 'Noombat' ]);
                $pet->label = 'noom!';
                $dialog = "Agh! This happens every year at about this time! Noombats everywhere! I don't know if it's Noombat breeding season, or what, but please adopt one of these things! If you insist, though, and ";
            }
            else if(CalendarFunctions::isLeapDay($this->clock->now))
            {
                $pet->species = $this->em->getRepository(PetSpecies::class)->findOneBy([
                    'name' => [
                        'Bear Frog',
                        'False Frog',
                        'Gelp',
                        'False Frog',
                    ][$i % 4]
                ]);
                $pet->label = '*ribbit*';
                $dialog = "Uh... your guess is as good as mine...\n\nAnd this rain feels unnatural, too, don't you think?\n\nWell... anyway, if ";
            }
            else if(RandomFunctions::squirrel3Noise($i + 100, $this->clock->now->format('YNmd')) % 200 === 1)
            {
                $pet->species = $squirrel3->rngNextFromArray(
                    $this->em->getRepository(PetSpecies::class)->findBy([
                        'availableFromPetShelter' => false,
                        'availableFromBreeding' => true
                    ])
                );

                $pet->label = [
                    'gasp!',
                    'oh my!',
                    'whoa!',
                    'ooooh!',
                    'omg!',
                ][RandomFunctions::squirrel3Noise($i, $this->clock->now->format('YNmd')) % 5];
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

    public static function isRarePetDay(\DateTimeImmutable $dt)
    {
        $numPets = self::getNumberOfPets($dt);

        for($i = 0; $i < $numPets; $i++)
        {
            if(RandomFunctions::squirrel3Noise($i + 100, $dt->format('YNmd')) % 200 === 1)
                return true;
        }

        return false;
    }

    public function numberOfSeasonalPets(int $totalPets, IRandom $squirrel3): int
    {
        $monthDay = $this->clock->getMonthAndDay();

        if(CalendarFunctions::isHalloween($this->clock->now))
            return $squirrel3->rngNextInt(1, 2);

        // PSP Thanksgiving overlaps Black Friday, but for pet adoption purposes, we want Black Friday to win out:
        if(CalendarFunctions::isBlackFriday($this->clock->now) || CalendarFunctions::isCyberMonday($this->clock->now))
            return ceil($totalPets / 2);

        if(CalendarFunctions::isThanksgiving($this->clock->now))
            return $squirrel3->rngNextInt(1, 2);

        if(CalendarFunctions::isEaster($this->clock->now))
            return $squirrel3->rngNextInt(1, 2);

        if(CalendarFunctions::isValentinesOrAdjacent($this->clock->now) || CalendarFunctions::isWhiteDay($this->clock->now))
            return 2;

        // winter solstice, more or less
        if($monthDay === 1221 || $monthDay === 1222)
            return ceil($totalPets / 2);

        // Christmas colors
        if($monthDay >= 1223 && $monthDay <= 1225)
            return $squirrel3->rngNextInt(1, 2);

        if(CalendarFunctions::isHanukkah($this->clock->now))
            return $squirrel3->rngNextInt(1, 2);

        if($this->chineseCalendarInfo->month === 1 && $this->chineseCalendarInfo->day <= 6)
            return 2;

        if(CalendarFunctions::isSaintPatricksDay($this->clock->now))
            return $squirrel3->rngNextInt(1, 3);

        return 0;
    }

    public function getSeasonalNames(): array
    {
        $monthDay = $this->clock->getMonthAndDay();

        if(CalendarFunctions::isHalloween($this->clock->now))
            return PetShelterPet::PET_HALLOWEEN_NAMES;

        // PSP Thanksgiving overlaps Black Friday, but for pet adoption purposes, we want Black Friday to win out:
        if(CalendarFunctions::isBlackFriday($this->clock->now))
            return PetShelterPet::PET_BLACK_FRIDAY_NAMES;

        if(CalendarFunctions::isCyberMonday($this->clock->now))
            return PetShelterPet::PET_CYBER_MONDAY_NAMES;

        if(CalendarFunctions::isThanksgiving($this->clock->now))
            return PetShelterPet::PET_THANKSGIVING_NAMES;

        if(CalendarFunctions::isEaster($this->clock->now))
            return PetShelterPet::PET_EASTER_NAMES;

        if(CalendarFunctions::isValentinesOrAdjacent($this->clock->now))
            return PetShelterPet::PET_VALENTINES_NAMES;

        if(CalendarFunctions::isWhiteDay($this->clock->now))
            return PetShelterPet::PET_WHITE_DAY_NAMES;

        // winter solstice, more or less
        if(CalendarFunctions::isWinterSolstice($this->clock->now))
            return PetShelterPet::PET_WINTER_SOLSTICE_NAMES;

        // Christmas colors (would normally do a 3-day range, but dec 23 isWinterSolstice())
        if($monthDay >= 1224 && $monthDay <= 1225)
            return PetShelterPet::PET_CHRISTMAS_NAMES;

        if(CalendarFunctions::isHanukkah($this->clock->now))
            return PetShelterPet::PET_HANUKKAH_NAMES;

        if($this->chineseCalendarInfo->month === 1 && $this->chineseCalendarInfo->day <= 6)
            return PetShelterPet::PET_CHINESE_ZODIAC_NAMES[$this->chineseCalendarInfo->animal];

        if(CalendarFunctions::isSaintPatricksDay($this->clock->now))
            return PetShelterPet::PET_NAMES;

        throw new \Exception('Today is not a day for seasonal colors.');
    }

    public function getSeasonalColors(): array
    {
        $monthDay = $this->clock->getMonthAndDay();

        if(CalendarFunctions::isHalloween($this->clock->now))
            return [ '333333', 'FF9933' ];

        // PSP Thanksgiving overlaps Black Friday, but for pet adoption purposes, we want Black Friday to win out:
        if(CalendarFunctions::isBlackFriday($this->clock->now))
            return [ '000000', '333333', '330000', '003300', '000033' ];

        if(CalendarFunctions::isCyberMonday($this->clock->now))
            return [ '000000', '005500', '00aa00', '00ff00' ];

        if(CalendarFunctions::isThanksgiving($this->clock->now))
            return [ 'CC6600', 'FFCC00', '009900', 'FF3300' ];

        if(CalendarFunctions::isEaster($this->clock->now))
            return [ 'FFCCFF', '99CCFF', 'FFFF99', 'FF9999' ];

        if(CalendarFunctions::isValentinesOrAdjacent($this->clock->now))
            return [ 'F17B7B', 'F8F8F8', 'FF0000', 'EF85FF' ];

        if(CalendarFunctions::isWhiteDay($this->clock->now))
            return [ 'FFFFFF', 'EEEEEE' ];

        // winter solstice, more or less
        if($monthDay === 1221 || $monthDay === 1222)
            return [ 'F8F8F8', '94C6F8' ];

        // Christmas colors
        if($monthDay >= 1223 && $monthDay <= 1225)
            return [ 'F8F8F8', 'CC3300', '009900' ];

        if(CalendarFunctions::isHanukkah($this->clock->now))
            return [ 'F8F8F8', '0066FF' ];

        if($this->chineseCalendarInfo->month === 1 && $this->chineseCalendarInfo->day <= 6)
            return [ 'CC232A', 'F5AC27', 'FFD84B', 'F2888B', 'A3262A', 'CC9902' ];

        if(CalendarFunctions::isSaintPatricksDay($this->clock->now))
            return [ '009900', '66CC66', '33AA00', '00AA33' ];

        throw new \Exception('Today is not a day for seasonal colors.');
    }
}
