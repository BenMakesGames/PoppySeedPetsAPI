<?php
namespace App\Service;

use App\Entity\User;
use App\Enum\UserStatEnum;
use App\Functions\ArrayFunctions;
use App\Functions\ColorFunctions;
use App\Functions\DateFunctions;
use App\Model\PetShelterPet;
use App\Repository\PetRepository;
use App\Repository\PetSpeciesRepository;
use App\Repository\UserStatsRepository;

class AdoptionService
{
    private $petRepository;
    private $petSpeciesRepository;
    private $calendarService;
    private $userStatsRepository;

    public function __construct(
        PetRepository $petRepository, PetSpeciesRepository $petSpeciesRepository, CalendarService $calendarService,
        UserStatsRepository $userStatsRepository
    )
    {
        $this->petRepository = $petRepository;
        $this->petSpeciesRepository = $petSpeciesRepository;
        $this->calendarService = $calendarService;
        $this->userStatsRepository = $userStatsRepository;
    }

    public function getAdoptionFee(User $user): int
    {
        $statValue = $this->userStatsRepository->getStatValue($user, UserStatEnum::PETS_ADOPTED);

        $itemsDonated = $this->userStatsRepository->getStatValue($user, UserStatEnum::ITEMS_DONATED_TO_MUSEUM);

        $bonus = 0;

        if($itemsDonated >= 300)
            $bonus += 5;

        if($itemsDonated >= 600)
            $bonus += 5;

        if($statValue <= 6)
            return 50 - $bonus;
        else if($statValue <= 28)
            return 75 - $bonus;
        else if($statValue <= 496)
            return 100 - $bonus;
        else if($statValue <= 8128)
            return 50 - $bonus;
        else
            return 20 - $bonus;
    }

    private function getNumberOfPets(User $user): int
    {
        $statValue = $this->userStatsRepository->getStatValue($user, UserStatEnum::PETS_ADOPTED);

        $bonus = $statValue > 6 && mt_rand(1, 31) === 1 ? 10 : 0;

        if($statValue <= 6)
            return mt_rand(4, 6) + $bonus;
        else if($statValue <= 28)
            return mt_rand(5, 8) + $bonus;
        else if($statValue <= 496)
            return mt_rand(6, 10) + $bonus;
        else if($statValue <= 8128)
            return mt_rand(6, 10) + $bonus;
        else
            return mt_rand(6, 10) + $bonus;
    }

    /**
     * @return PetShelterPet[]
     */
    public function getDailyPets(User $user): array
    {
        $now = new \DateTimeImmutable();
        $nowString = $now->format('Y-m-d');

        mt_srand($user->getDailySeed());

        $numPets = $this->getNumberOfPets($user);
        $numSeasonalPets = $this->numberOfSeasonalPets($numPets);

        $petCount = $this->petRepository->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->andWhere('p.birthDate<:today')
            ->setParameter('today', $nowString)
            ->getQuery()
            ->getSingleScalarResult();
        ;

        $isBlueMoon = DateFunctions::getFullMoonName($now) === 'Blue';
        $pets = [];

        $allSpecies = $this->petSpeciesRepository->findBy([ 'availableFromPetShelter' => true ]);

        for($i = 0; $i < $numPets; $i++)
        {
            if($i < $numSeasonalPets)
            {
                $colors = $this->getSeasonalColors();

                shuffle($colors);

                $colorA = ColorFunctions::tweakColor($colors[0]);
                $colorB = ColorFunctions::tweakColor($colors[1]);

                $name = ArrayFunctions::pick_one($this->getSeasonalNames());
            }
            else if($i === $numPets - 1 && !$isBlueMoon)
            {
                // RANDOM!
                $h1 = mt_rand(0, 1000) / 1000.0;
                $s1 = mt_rand(mt_rand(0, 500), 1000) / 1000.0;
                $l1 = mt_rand(mt_rand(0, 500), mt_rand(750, 1000)) / 1000.0;

                $h2 = mt_rand(0, 1000) / 1000.0;
                $s2 = mt_rand(mt_rand(0, 500), 1000) / 1000.0;
                $l2 = mt_rand(mt_rand(0, 500), mt_rand(750, 1000)) / 1000.0;

                $colorA = ColorFunctions::HSL2Hex($h1, $s1, $l1);
                $colorB = ColorFunctions::HSL2Hex($h2, $s2, $l2);

                $name = ArrayFunctions::pick_one(PetShelterPet::PET_NAMES);
            }
            else
            {
                if($isBlueMoon)
                {
                    $blueA = mt_rand(127, 255);
                    $otherA = mt_rand(0, $blueA - 16);

                    $blueB = mt_rand(127, 255);
                    $otherB = mt_rand(0, $blueB - 16);

                    $colorA = ColorFunctions::RGB2Hex($otherA, $otherA, $blueA);
                    $colorB = ColorFunctions::RGB2Hex($otherB, $otherB, $blueB);

                    $colorA = ColorFunctions::tweakColor($colorA);
                    $colorB = ColorFunctions::tweakColor($colorB);
                }
                else
                {
                    $basePet = $this->petRepository->createQueryBuilder('p')
                        ->andWhere('p.birthDate<:today')
                        ->setParameter('today', $nowString)
                        ->setMaxResults(1)
                        ->setFirstResult(mt_rand(0, $petCount - 1))
                        ->getQuery()
                        ->getSingleResult();

                    $colorA = ColorFunctions::tweakColor($basePet->getColorA());
                    $colorB = ColorFunctions::tweakColor($basePet->getColorB());
                }

                if($this->calendarService->isPiDay())
                    $name = ArrayFunctions::pick_one([ 'Pi',  'Pi', 'Pie', 'Pie', 'Pie', 'Pie', 'Pie', 'Cake' ]);
                else
                    $name = ArrayFunctions::pick_one(PetShelterPet::PET_NAMES);
            }

            $pet = new PetShelterPet();

            if($this->calendarService->isHalloweenDay())
            {
                $pet->species = $this->petSpeciesRepository->findOneBy([ 'name' => 'Fog Elemental' ]);
                $pet->label = 'spooky!';
            }
            else if(mt_rand(1, 200) === 1)
            {
                $pet->species = ArrayFunctions::pick_one($this->petSpeciesRepository->findBy(['availableFromPetShelter' => false, 'availableFromBreeding' => true]));
                $pet->label = ArrayFunctions::pick_one([
                    'gasp!',
                    'oh my!',
                    'whoa!',
                    'ooooh!',
                ]);
            }
            else
                $pet->species = ArrayFunctions::pick_one($allSpecies);

            $pet->name = $name;
            $pet->colorA = $colorA;
            $pet->colorB = $colorB;
            $pet->id = mt_rand(100000, 999999) * 10 + $i;
            $pet->scale = mt_rand(80, 120);

            $pets[] = $pet;
        }

        return $pets;
    }

    public function numberOfSeasonalPets(int $totalPets): int
    {
        $monthDay = $this->calendarService->getMonthAndDay();

        if($this->calendarService->isHalloween())
            return mt_rand(1, 2);

        if($this->calendarService->isThanksgiving())
            return mt_rand(1, 2);

        if($this->calendarService->isEaster())
            return mt_rand(1, 2);

        if($this->calendarService->isValentines() || $this->calendarService->isWhiteDay())
            return 2;

        // winter solstice, more or less
        if($monthDay === 1221 || $monthDay === 1222)
            return ceil($totalPets / 2);

        // christmas colors
        if($monthDay >= 1223 && $monthDay <= 1225)
            return mt_rand(1, 2);

        if($this->calendarService->isHannukah())
            return mt_rand(1, 2);

        return 0;
    }

    public function getSeasonalNames(): array
    {
        $monthDay = $this->calendarService->getMonthAndDay();

        if($this->calendarService->isHalloween())
            return PetShelterPet::PET_HALLOWEEN_NAMES;

        if($this->calendarService->isThanksgiving())
            return PetShelterPet::PET_THANKSGIVING_NAMES;

        if($this->calendarService->isEaster())
            return PetShelterPet::PET_EASTER_NAMES;

        if($this->calendarService->isValentines())
            return PetShelterPet::PET_VALENTINES_NAMES;

        if($this->calendarService->isWhiteDay())
            return PetShelterPet::PET_WHITE_DAY_NAMES;

        // winter solstice, more or less
        if($monthDay === 1221 || $monthDay === 1222)
            return PetShelterPet::PET_SOLSTICE_NAMES;

        // christmas colors
        if($monthDay >= 1223 && $monthDay <= 1225)
            return PetShelterPet::PET_CHRISTMAS_NAMES;

        if($this->calendarService->isHannukah())
            return PetShelterPet::PET_HANNUKAH_NAMES;

        throw new \InvalidArgumentException('Today is not a day for seasonal colors.');
    }

    public function getSeasonalColors(): array
    {
        $monthDay = $this->calendarService->getMonthAndDay();

        if($this->calendarService->isHalloween())
            return $this->getHalloweenColors();

        if($this->calendarService->isThanksgiving())
            return $this->getThanksgivingColors();

        if($this->calendarService->isEaster())
            return $this->getEasterColors();

        if($this->calendarService->isValentines())
            return $this->getValentinesColors();

        if($this->calendarService->isWhiteDay())
            return $this->getWhiteDayColors();

        // winter solstice, more or less
        if($monthDay === 1221 || $monthDay === 1222)
            return $this->getWinterSolsticeColors();

        // christmas colors
        if($monthDay >= 1223 && $monthDay <= 1225)
            return $this->getChristmasColors();

        if($this->calendarService->isHannukah())
            return $this->getHannukahColors();

        throw new \InvalidArgumentException('Today is not a day for seasonal colors.');
    }

    public function getHalloweenColors(): array
    {
        // black and orange
        return [ '333333', 'FF9933' ];
    }

    public function getWinterSolsticeColors(): array
    {
        return [ 'F8F8F8', '94C6F8' ];
    }

    public function getChristmasColors(): array
    {
        return [ 'F8F8F8', 'CC3300', '009900' ];
    }

    public function getHannukahColors(): array
    {
        return [ 'F8F8F8', '0066FF' ];
    }

    public function getThanksgivingColors(): array
    {
        return [ 'CC6600', 'FFCC00', '009900', 'FF3300' ];
    }

    public function getEasterColors(): array
    {
        return [ 'FFCCFF', '99CCFF', 'FFFF99', 'FF9999' ];
    }

    public function getValentinesColors(): array
    {
        return [ 'F17B7B', 'F8F8F8', 'FF0000', 'EF85FF' ];
    }

    public function getWhiteDayColors(): array
    {
        return [ 'FFFFFF', 'EEEEEE' ];
    }
}
