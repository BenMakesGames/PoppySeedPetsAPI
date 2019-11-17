<?php
namespace App\Service;

use App\Entity\User;
use App\Functions\ArrayFunctions;
use App\Model\PetShelterPet;
use App\Repository\PetRepository;
use App\Repository\PetSpeciesRepository;

class AdoptionService
{
    private $petRepository;
    private $petSpeciesRepository;
    private $calendarService;

    public const PET_NAMES = [
        'Aalina', 'Aaron', 'Abrahil', 'Aedoc', 'Aelfric', 'Alain', 'Alda', 'Alienora', 'Aliette', 'Artaca',
        'Aureliana', 'Batu', 'Belka', 'Bezzhen', 'Biedeluue', 'Blicze', 'Ceinguled', 'Ceri', 'Ceslinus', 'Christien',
        'Clement', 'Cyra', 'Czestobor', 'Dagena', 'Denyw', 'Disideri', 'Eileve', 'Emilija', 'Enim', 'Enynny',
        'Erasmus', 'Eve', 'Felix', 'Fiora', 'Fluri', 'Frotlildis', 'Galine', 'Gennoveus', 'Genoveva', 'Giliana',
        'Godelive', 'Gubin', 'Idzi', 'Jadviga', 'Jehanne', 'Kaija', 'Kain', 'Kima', 'Kint', 'Kirik',
        'Klara', 'Kryspin', 'Leodhild', 'Leon', 'Levi', 'Lowri', 'Lucass', 'Ludmila', 'Maccos', 'Maeldoi',
        'Magdalena', 'Makrina', 'Malik', 'Margaret', 'Marsley', 'Masayasu', 'Mateline', 'Mathias', 'Maurifius', 'Meduil',
        'Melita', 'Meoure', 'Merewen', 'Milesent', 'Milian', 'Mold', 'Montgomery', 'Morys', 'Newt', 'Nicholina',
        'Nilus', 'Noe', 'Oswyn', 'Paperclip', 'Perkhta', 'Pesczek', 'Regina', 'Reina', 'Rimoete', 'Rocatos',
        'Rozalia', 'Rum', 'Runne', 'Ryd', 'Saewine', 'Sandivoi', 'Skenfrith', 'Sulimir', 'Sybil', 'Talan',
        'Tede', 'Tephaine', 'Tetris', 'Tiecia', 'Toregene', 'Trenewydd', 'Usk', 'Vasilii', 'Vitseslav', 'Vivka',
        'Wrexham', 'Ysabeau', 'Ystradewel', 'Zofija', 'Zygmunt'
    ];

    public const PET_HALLOWEEN_NAMES = [
        'Pumpkin', 'Luna', 'Magic', 'Bones', 'Haunt', 'Spirit', 'Cauldron', 'Werewolf', 'Vampire',
    ];

    public const PET_CHRISTMAS_NAMES = [
        'Holly', 'Cocoa', 'Evergreen', 'Santa', 'Dasher', 'Dancer', 'Prancer', 'Vixen', 'Comet', 'Cupid', 'Donner',
        'Blitzen', 'Rudolph', 'Olive', 'Spirit', 'Mint', 'Sol Invictus',
    ];

    public const PET_THANKSGIVING_NAMES = [
        'Gobbles', 'Pumpkin', 'Cranberry', 'Turkey', 'Stuffing', 'Potato', 'Gravy',
    ];

    public const PET_HANNUKAH_NAMES = [
        'Dreidel', 'Olive Oil', 'Potato', 'Pancake', 'Gelt', 'Maccabee', 'Pączki', 'Buñuelo', 'Sufganiyah',
    ];

    public const PET_EASTER_NAMES = [
        'Osterbaum', 'Bunny', 'Rabbit', 'Daffodil', 'Lamb', 'Pastel',
    ];

    public const PET_SOLSTICE_NAMES = [
        'Solstice', 'Midwinter', 'Makara', 'Yaldā', 'Yule', 'Dongzhi',
    ];

    public function __construct(
        PetRepository $petRepository, PetSpeciesRepository $petSpeciesRepository, CalendarService $calendarService
    )
    {
        $this->petRepository = $petRepository;
        $this->petSpeciesRepository = $petSpeciesRepository;
        $this->calendarService = $calendarService;
    }

    /**
     * @return PetShelterPet[]
     */
    public function getDailyPets(User $user): array
    {
        $now = (new \DateTimeImmutable())->format('Y-m-d');

        mt_srand($user->getDailySeed());

        $numPets = mt_rand(4, 8);
        $numSeasonalPets = $this->numberOfSeasonalPets($numPets);

        $petCount = $this->petRepository->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->andWhere('p.birthDate<:today')
            ->setParameter('today', $now)
            ->getQuery()
            ->getSingleScalarResult();
        ;

        $pets = [];

        $allSpecies = $this->petSpeciesRepository->findBy([ 'availableFromPetShelter' => true ]);

        for($i = 0; $i < $numPets; $i++)
        {
            if($i < $numSeasonalPets)
            {
                $colors = $this->getSeasonalColors();

                shuffle($colors);

                $colorA = $this->tweakColor($colors[0]);
                $colorB = $this->tweakColor($colors[1]);

                $name = ArrayFunctions::pick_one($this->getSeasonalNames());
            }
            else
            {
                $basePet = $this->petRepository->createQueryBuilder('p')
                    ->andWhere('p.birthDate<:today')
                    ->setParameter('today', $now)
                    ->setMaxResults(1)
                    ->setFirstResult(mt_rand(0, $petCount - 1))
                    ->getQuery()
                    ->getSingleResult()
                ;

                $colorA = $this->tweakColor($basePet->getColorA());
                $colorB = $this->tweakColor($basePet->getColorB());

                $name = ArrayFunctions::pick_one(self::PET_NAMES);
            }

            $pet = new PetShelterPet();
            $pet->species = ArrayFunctions::pick_one($allSpecies);
            $pet->name = $name;
            $pet->colorA = $colorA;
            $pet->colorB = $colorB;
            $pet->id = mt_rand(100000, 999999) * 10 + $i;

            $pets[] = $pet;
        }

        return $pets;
    }

    private function tweakColor(string $color): string
    {
        $newColor = '';

        for($i = 0; $i < 3; $i++)
        {
            $part = hexdec($color[$i * 2] . $color[$i * 2 + 1]);    // get color part as decimal
            $part += mt_rand(-12, 12);                              // randomize
            $part = max(0, min(255, $part));                        // keep between 0 and 255
            $part = str_pad(dechex($part), 2, '0', STR_PAD_LEFT);   // turn back into hex

            $newColor .= $part;
        }

        return $newColor;
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

        // winter solstice, more or less
        if($monthDay === 1221 || $monthDay === 1222)
            return $totalPets - 1;

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
            return self::PET_HALLOWEEN_NAMES;

        if($this->calendarService->isThanksgiving())
            return self::PET_THANKSGIVING_NAMES;

        if($this->calendarService->isEaster())
            return self::PET_EASTER_NAMES;

        // winter solstice, more or less
        if($monthDay === 1221 || $monthDay === 1222)
            return self::PET_SOLSTICE_NAMES;

        // christmas colors
        if($monthDay >= 1223 && $monthDay <= 1225)
            return self::PET_CHRISTMAS_NAMES;

        if($this->calendarService->isHannukah())
            return self::PET_HANNUKAH_NAMES;

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
        return [ '333333', 'FF9999' ];
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
}