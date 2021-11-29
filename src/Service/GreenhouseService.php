<?php
namespace App\Service;

use App\Entity\Greenhouse;
use App\Entity\GreenhousePlant;
use App\Entity\Merit;
use App\Entity\PetSpecies;
use App\Entity\User;
use App\Enum\BirdBathBirdEnum;
use App\Enum\FlavorEnum;
use App\Enum\LocationEnum;
use App\Enum\MeritEnum;
use App\Enum\PetLocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Enum\UserStatEnum;
use App\Functions\ArrayFunctions;
use App\Repository\GreenhousePlantRepository;
use App\Repository\InventoryRepository;
use App\Repository\MeritRepository;
use App\Repository\PetRepository;
use App\Repository\PetSpeciesRepository;
use App\Repository\UserQuestRepository;
use App\Repository\UserStatsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class GreenhouseService
{
    private $inventoryService;
    private $petRepository;
    private $petFactory;
    private $em;
    private $meritRepository;
    private $userStatsRepository;
    private IRandom $squirrel3;
    private UserQuestRepository $userQuestRepository;
    private GreenhousePlantRepository $greenhousePlantRepository;
    private InventoryRepository $inventoryRepository;
    private NormalizerInterface $normalizer;
    private PetSpeciesRepository $petSpeciesRepository;

    public function __construct(
        InventoryService $inventoryService, PetRepository $petRepository, PetFactory $petFactory, Squirrel3 $squirrel3,
        EntityManagerInterface $em, MeritRepository $meritRepository, UserStatsRepository $userStatsRepository,
        UserQuestRepository $userQuestRepository, GreenhousePlantRepository $greenhousePlantRepository,
        InventoryRepository $inventoryRepository, NormalizerInterface $normalizer,
        PetSpeciesRepository $petSpeciesRepository
    )
    {
        $this->inventoryService = $inventoryService;
        $this->petRepository = $petRepository;
        $this->petFactory = $petFactory;
        $this->em = $em;
        $this->meritRepository = $meritRepository;
        $this->userStatsRepository = $userStatsRepository;
        $this->squirrel3 = $squirrel3;
        $this->userQuestRepository = $userQuestRepository;
        $this->greenhousePlantRepository = $greenhousePlantRepository;
        $this->inventoryRepository = $inventoryRepository;
        $this->normalizer = $normalizer;
        $this->petSpeciesRepository = $petSpeciesRepository;
    }

    public function approachBird(Greenhouse $greenhouse): string
    {
        $user = $greenhouse->getOwner();

        switch($greenhouse->getVisitingBird())
        {
            case BirdBathBirdEnum::OWL:
                $scroll = $this->squirrel3->rngNextFromArray([
                    'Behatting Scroll',
                    'Behatting Scroll',
                    'Behatting Scroll',
                    'Renaming Scroll',
                    'Renaming Scroll',
                    'Forgetting Scroll',
                ]);

                $this->inventoryService->receiveItem($scroll, $user, $user, 'Left behind by a huge owl that visited ' . $user->getName() . '\'s Bird Bath.', LocationEnum::HOME);
                $message = 'As you approach the owl, it tilts its head at you. You freeze, and stare at each other for a few seconds before the owl flies off, dropping some kind of scroll as it goes!';
                break;

            case BirdBathBirdEnum::RAVEN:
                $this->inventoryService->receiveItem('Black Feathers', $user, $user, 'Left behind by a huge raven that visited ' . $user->getName() . '\'s Bird Bath.', LocationEnum::HOME);
                $message = 'As you approach the raven, it turns to face you. You freeze, and stare at each other for a few seconds before the raven flies off in a flurry of Black Feathers!';
                break;

            case BirdBathBirdEnum::TOUCAN:
                $this->inventoryService->receiveItem('Cereal Box', $user, $user, 'Left behind by a huge toucan that visited ' . $user->getName() . '\'s Bird Bath.', LocationEnum::HOME);
                $message = 'As you approach the toucan, it turns to face you. You freeze, and stare at each other for a few seconds before the toucan flies off, leaving a Cereal Box behind.';
                break;

            default:
                throw new \Exception('Ben has done something wrong, and not accounted for this type of bird!');
        }

        $greenhouse->setVisitingBird(null);

        $this->userStatsRepository->incrementStat($user, UserStatEnum::LARGE_BIRDS_APPROACHED);

        return $message;
    }

    public function harvestPlantAsPet(GreenhousePlant $plant, PetSpecies $species, string $colorA, string $colorB, string $name, ?Merit $bonusMerit): string
    {
        $user = $plant->getOwner();

        $message = 'You harvested-- WHOA, WAIT, WHAT?! It\'s a living ' . $species->getName() . '!?';

        $numberOfPetsAtHome = $this->petRepository->getNumberAtHome($user);

        $startingMerits = MeritRepository::POSSIBLE_STARTING_MERITS;

        if($bonusMerit)
        {
            $startingMerits = array_filter($startingMerits, fn($m) =>
                $m !== $bonusMerit->getName()
            );
        }

        $startingMerit = $this->meritRepository->findOneByName($this->squirrel3->rngNextFromArray($startingMerits));

        $harvestedPet = $this->petFactory->createPet($user, $name, $species, $colorA, $colorB, FlavorEnum::getRandomValue($this->squirrel3), $startingMerit);

        if($bonusMerit)
            $harvestedPet->addMerit($bonusMerit);

        $harvestedPet
            ->setFoodAndSafety($this->squirrel3->rngNextInt(10, 12), -9)
            ->setScale($this->squirrel3->rngNextInt(80, 120))
        ;

        $this->em->remove($plant);

        if($numberOfPetsAtHome >= $user->getMaxPets())
        {
            $message .= "\n\n" . 'Seeing no space in your house, the creature wanders off to Daycare.';
            $harvestedPet->setLocation(PetLocationEnum::DAYCARE);
        }
        else
        {
            $message .= "\n\n" . 'The creature wastes no time in setting up residence in your house.';
            $harvestedPet->setLocation(PetLocationEnum::HOME);
        }

        return $message;
    }

    public function getGreenhouseResponseData(User $user)
    {
        $fertilizers = $this->inventoryRepository->findFertilizers($user);

        return [
            'greenhouse' => $user->getGreenhouse(),
            'weeds' => $this->getWeedText($user),
            'plants' => $this->greenhousePlantRepository->findBy([ 'owner' => $user->getId() ]),
            'fertilizer' => $this->normalizer->normalize($fertilizers, null, [ 'groups' => [ SerializationGroupEnum::GREENHOUSE_FERTILIZER ] ]),
        ];
    }

    public function getWeedText(User $user)
    {
        $weeds = $this->userQuestRepository->findOrCreate($user, 'Greenhouse Weeds', (new \DateTimeImmutable())->modify('-1 minutes')->format('Y-m-d H:i:s'));

        $weedTime = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $weeds->getValue());

        if($weedTime > new \DateTimeImmutable())
            $weedText = null;
        else
        {
            $weedText = $this->squirrel3->rngNextFromArray([
                'Don\'t need \'em; don\'t want \'em!',
                'Get outta\' here, weeds!',
                'Weeds can gtfo!',
                'WEEEEEEDS!! *shakes fist*',
                'Exterminate! EXTERMINATE!',
                'Destroy all weeds!',
            ]);
        }

        if(!$weeds->getId())
            $this->em->flush();

        return $weedText;
    }

    public function makeDapperSwanPet(GreenhousePlant $plant): string
    {
        $species = $this->petSpeciesRepository->findOneBy([ 'name' => 'Dapper Swan' ]);

        $colorA = $this->squirrel3->rngNextTweakedColor($this->squirrel3->rngNextFromArray([
            'EEEEEE', 'EEDDCC', 'DDDDBB'
        ]));

        $colorB = $this->squirrel3->rngNextTweakedColor($this->squirrel3->rngNextFromArray([
            'bb0000', '33CCFF', '009900', 'CC9933', '333333'
        ]));

        if($this->squirrel3->rngNextInt(1, 3) === 1)
        {
            $temp = $colorA;
            $colorA = $colorB;
            $colorB = $temp;
        }

        $name = $this->squirrel3->rngNextFromArray([
            'Gosling', 'Goose', 'Honks', 'Clamshell', 'Mussel', 'Seafood', 'Nauplius', 'Mr. Beaks',
            'Medli', 'Buff', 'Tuft', 'Tail-feather', 'Anser', 'Cygnus', 'Paisley', 'Bolo', 'Cravat',
            'Ascot', 'Neckerchief'
        ]);

        $bonusMerit = $this->meritRepository->findOneByName(MeritEnum::MOON_BOUND);

        return $this->harvestPlantAsPet($plant, $species, $colorA, $colorB, $name, $bonusMerit);
    }

    public function makeMushroomPet(GreenhousePlant $plant)
    {
        $species = $this->petSpeciesRepository->findOneBy([ 'name' => 'Mushroom' ]);

        $colorA = $this->squirrel3->rngNextTweakedColor($this->squirrel3->rngNextFromArray([
            'e32c2c', 'e5e5d6', 'dd8a09', 'a8443d'
        ]));

        $colorB = $this->squirrel3->rngNextTweakedColor($this->squirrel3->rngNextFromArray([
            'd7d38b', 'e5e5d6', '716363'
        ]));

        if($this->squirrel3->rngNextInt(1, 4) === 1)
        {
            $temp = $colorA;
            $colorA = $colorB;
            $colorB = $temp;
        }

        $name = $this->squirrel3->rngNextFromArray([
            'Cremini', 'Button', 'Portobello', 'Oyster', 'Porcini', 'Morel', 'Enoki', 'Shimeji',
            'Shiitake', 'Maitake', 'Reishi', 'Puffball', 'Galerina', 'Gypsy', 'Milkcap', 'Bolete',
            'Honey', 'Pinewood', 'Horse', 'Périgord', 'Tooth', 'Blewitt', 'Pom Pom', 'Ear', 'Jelly',
            'Chestnut', 'Khumbhi', 'Helvella', 'Amanita'
        ]);

        $bonusMerit = $this->meritRepository->findOneByName(MeritEnum::DARKVISION);

        return $this->harvestPlantAsPet($plant, $species, $colorA, $colorB, $name, $bonusMerit);
    }

    public function makeTomatePet(GreenhousePlant $plant)
    {
        $species = $this->petSpeciesRepository->findOneBy([ 'name' => 'Tomate' ]);

        $colorA = $this->squirrel3->rngNextTweakedColor($this->squirrel3->rngNextFromArray([
            'FF6622', 'FFCC22', '77FF22', 'FF2222', '7722FF'
        ]));

        $colorB = $this->squirrel3->rngNextTweakedColor($this->squirrel3->rngNextFromArray([
            '007700', '009922', '00bb44'
        ]));

        $name = $this->squirrel3->rngNextFromArray([
            'Alicante', 'Azoychka', 'Krim', 'Brandywine', 'Campari', 'Canario', 'Tomkin',
            'Flamenco', 'Giulietta', 'Grandero', 'Trifele', 'Jubilee', 'Juliet', 'Kumato',
            'Monterosa', 'Montserrat', 'Plum', 'Raf', 'Roma', 'Rutgers', 'Marzano', 'Cherry',
            'Nebula', 'Santorini', 'Tomaccio', 'Tamatie', 'Tamaatar', 'Matomatisi', 'Yaanyo',
            'Pomidor', 'Utamatisi'
        ]);

        $bonusMerit = $this->meritRepository->findOneByName(MeritEnum::MOON_BOUND);

        return $this->harvestPlantAsPet($plant, $species, $colorA, $colorB, $name, $bonusMerit);
    }
}
