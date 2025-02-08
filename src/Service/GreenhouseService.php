<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Greenhouse;
use App\Entity\GreenhousePlant;
use App\Entity\Inventory;
use App\Entity\Merit;
use App\Entity\PetSpecies;
use App\Entity\User;
use App\Enum\BirdBathBirdEnum;
use App\Enum\FlavorEnum;
use App\Enum\LocationEnum;
use App\Enum\MeritEnum;
use App\Enum\PetLocationEnum;
use App\Enum\PollinatorEnum;
use App\Enum\SerializationGroupEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Enum\UserStatEnum;
use App\Functions\ArrayFunctions;
use App\Functions\MeritRepository;
use App\Functions\PlayerLogFactory;
use App\Functions\SpiceRepository;
use App\Functions\UserQuestRepository;
use App\Model\MeritInfo;
use App\Repository\InventoryRepository;
use App\Repository\PetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class GreenhouseService
{
    public function __construct(
        private readonly InventoryService $inventoryService,
        private readonly PetFactory $petFactory,
        private readonly IRandom $squirrel3,
        private readonly EntityManagerInterface $em,
        private readonly UserStatsService $userStatsRepository,
        private readonly NormalizerInterface $normalizer,
        private readonly Clock $clock
    )
    {
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
                $activityLogMessage = 'You approached an owl in your birdbath! It flew off, leaving behind a ' . $scroll . '!';
                break;

            case BirdBathBirdEnum::RAVEN:
                $this->inventoryService->receiveItem('Black Feathers', $user, $user, 'Left behind by a huge raven that visited ' . $user->getName() . '\'s Bird Bath.', LocationEnum::HOME);
                $this->inventoryService->receiveItem('Black Feathers', $user, $user, 'Left behind by a huge raven that visited ' . $user->getName() . '\'s Bird Bath.', LocationEnum::HOME);
                $extraItem = $this->squirrel3->rngNextFromArray([
                    'Juice Box',
                    $this->squirrel3->rngNextFromArray([ 'Winged Key', 'Piece of Cetgueli\'s Map' ]),
                    $this->squirrel3->rngNextFromArray([ 'Magic Smoke', 'Lightning in a Bottle' ]),
                ]);
                $extraInventory = $this->inventoryService->receiveItem($extraItem, $user, $user, 'Left behind by a huge raven that visited ' . $user->getName() . '\'s Bird Bath.', LocationEnum::HOME);
                $message = 'As you approach the raven, it turns to face you. You freeze, and stare at each other for a few seconds before the raven flies off in a flurry of Black Feathers! Also, it apparently left ' . $extraInventory->getItem()->getNameWithArticle() . ' behind? \'Kay...';
                $activityLogMessage = 'You approached a raven in your birdbath! It flew off, leaving behind some Black Feathers, and ' . $extraInventory->getItem()->getNameWithArticle() . '!';
                break;

            case BirdBathBirdEnum::TOUCAN:
                $this->inventoryService->receiveItem('Cereal Box', $user, $user, 'Left behind by a huge toucan that visited ' . $user->getName() . '\'s Bird Bath.', LocationEnum::HOME);
                $this->inventoryService->receiveItem('Scroll of Fruit', $user, $user, 'Left behind by a huge toucan that visited ' . $user->getName() . '\'s Bird Bath.', LocationEnum::HOME);
                $message = 'As you approach the toucan, it turns to face you. You freeze, and stare at each other for a few seconds before the toucan flies off, leaving behind a Cereal Box, and a Scroll of Fruit! (Presumably as part of a complete breakfast!)';
                $activityLogMessage = 'You approached a toucan in your birdbath! It flew off, leaving behind a Cereal Box, and a Scroll of Fruit! (Presumably as part of a complete breakfast!)';
                break;

            default:
                throw new \Exception('Ben has done something wrong, and not accounted for this type of bird in the code! BEN! HOW COULD LET US DOWN LIKE THIS???');
        }

        $greenhouse->setVisitingBird(null);

        $this->userStatsRepository->incrementStat($user, UserStatEnum::LARGE_BIRDS_APPROACHED);

        PlayerLogFactory::create($this->em, $user, $activityLogMessage, [ 'Greenhouse', 'Birdbath' ]);

        return $message;
    }

    public function applyPollinatorSpice(Inventory $item, string $pollinators)
    {
        if($pollinators === PollinatorEnum::BEES_1 || $pollinators === PollinatorEnum::BEES_2)
            $spiceName = $this->squirrel3->rngNextInt(1, 20) === 1 ? 'of Queens' : 'Anthophilan';
        else if($pollinators === PollinatorEnum::BUTTERFLIES)
            $spiceName = $this->squirrel3->rngNextFromArray([ 'Fortified', 'Nectarous' ]);
        else
            throw new \InvalidArgumentException('Programmer foolishness did not account for all pollinators when applying spices!');

        $item->setSpice(SpiceRepository::findOneByName($this->em, $spiceName));
    }

    public function harvestPlantAsPet(GreenhousePlant $plant, PetSpecies $species, string $colorA, string $colorB, string $name, ?Merit $bonusMerit): string
    {
        $user = $plant->getOwner();

        $message = 'You harvested-- WHOA, WAIT, WHAT?! It\'s a living ' . $species->getName() . '!?';

        $numberOfPetsAtHome = PetRepository::getNumberAtHome($this->em, $user);

        $startingMerits = MeritInfo::POSSIBLE_STARTING_MERITS;

        if($bonusMerit)
        {
            $startingMerits = array_filter($startingMerits, fn($m) =>
                $m !== $bonusMerit->getName()
            );
        }

        $startingMerit = MeritRepository::findOneByName($this->em, $this->squirrel3->rngNextFromArray($startingMerits));

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

    public function maybeAssignPollinators(User $user)
    {
        $twoHoursAgo = $this->clock->now->sub(\DateInterval::createFromDateString('2 hours'));

        if($user->getGreenhouse()->getButterfliesDismissedOn() <= $twoHoursAgo)
            $this->maybeAssignPollinator($user, PollinatorEnum::BUTTERFLIES);

        if($user->hasUnlockedFeature(UnlockableFeatureEnum::Beehive))
        {
            if($user->getGreenhouse()->getBeesDismissedOn() <= $twoHoursAgo)
                $this->maybeAssignPollinator($user, PollinatorEnum::BEES_1);

            if($user->getBeehive() && $user->getBeehive()->getWorkers() >= 500 && $user->getGreenhouse()->getBees2DismissedOn() <= $twoHoursAgo)
                $this->maybeAssignPollinator($user, PollinatorEnum::BEES_2);
        }
    }

    private function maybeAssignPollinator(User $user, string $pollinator): bool
    {
        // must not already have this pollinator present
        if(ArrayFunctions::any($user->getGreenhousePlants(), fn(GreenhousePlant $p) => $p->getPollinators() == $pollinator))
            return false;

        // must have at least 3 generally-pollinatable plants
        $availablePlants = array_filter($user->getGreenhousePlants()->toArray(), fn(GreenhousePlant $p) => !$p->getPlant()->getNoPollinators());

        if($availablePlants < 3)
            return false;

        // must have at least 1 plant available
        $availablePlants = array_filter($availablePlants, fn(GreenhousePlant $p) => $p->getPollinators() == null);

        if(count($availablePlants) === 0)
            return false;

        /** @var GreenhousePlant $plant */
        $plant = $this->squirrel3->rngNextFromArray($availablePlants);

        $plant->setPollinators($pollinator);

        return true;
    }

    public function getGreenhouseResponseData(User $user): array
    {
        $fertilizers = InventoryRepository::findFertilizers($this->em, $user);

        return [
            'greenhouse' => $user->getGreenhouse(),
            'weeds' => $this->getWeedText($user),
            'plants' => $this->em->getRepository(GreenhousePlant::class)->findBy([ 'owner' => $user->getId() ]),
            'fertilizer' => $this->normalizer->normalize($fertilizers, null, [ 'groups' => [ SerializationGroupEnum::GREENHOUSE_FERTILIZER ] ]),
        ];
    }

    public function getWeedText(User $user): ?string
    {
        $weeds = UserQuestRepository::findOrCreate($this->em, $user, 'Greenhouse Weeds', $this->clock->now->modify('-1 minutes')->format('Y-m-d H:i:s'));

        $weedTime = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $weeds->getValue());

        if($weedTime > $this->clock->now)
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
        $species = $this->em->getRepository(PetSpecies::class)->findOneBy([ 'name' => 'Dapper Swan' ]);

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

        $bonusMerit = MeritRepository::findOneByName($this->em, MeritEnum::MOON_BOUND);

        return $this->harvestPlantAsPet($plant, $species, $colorA, $colorB, $name, $bonusMerit);
    }

    public function makeMushroomPet(GreenhousePlant $plant)
    {
        $species = $this->em->getRepository(PetSpecies::class)->findOneBy([ 'name' => 'Mushroom' ]);

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
            'Shiitake', 'Maitake', 'Reishi', 'Puffball', 'Galerina', 'Milkcap', 'Bolete',
            'Honey', 'Pinewood', 'Horse', 'Périgord', 'Tooth', 'Blewitt', 'Pom Pom', 'Ear', 'Jelly',
            'Chestnut', 'Khumbhi', 'Helvella', 'Amanita'
        ]);

        $bonusMerit = MeritRepository::findOneByName($this->em, MeritEnum::DARKVISION);

        return $this->harvestPlantAsPet($plant, $species, $colorA, $colorB, $name, $bonusMerit);
    }

    public function makeTomatePet(GreenhousePlant $plant)
    {
        $species = $this->em->getRepository(PetSpecies::class)->findOneBy([ 'name' => 'Tomate' ]);

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

        $bonusMerit = MeritRepository::findOneByName($this->em, MeritEnum::MOON_BOUND);

        return $this->harvestPlantAsPet($plant, $species, $colorA, $colorB, $name, $bonusMerit);
    }
}
