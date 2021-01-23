<?php
namespace App\Service;

use App\Entity\Greenhouse;
use App\Entity\GreenhousePlant;
use App\Entity\Merit;
use App\Entity\PetSpecies;
use App\Enum\BirdBathBirdEnum;
use App\Enum\FlavorEnum;
use App\Enum\LocationEnum;
use App\Enum\MeritEnum;
use App\Enum\UserStatEnum;
use App\Functions\ArrayFunctions;
use App\Repository\MeritRepository;
use App\Repository\PetRepository;
use App\Repository\UserStatsRepository;
use Doctrine\ORM\EntityManagerInterface;

class GreenhouseService
{
    private $inventoryService;
    private $petRepository;
    private $petFactory;
    private $em;
    private $meritRepository;
    private $userStatsRepository;
    private $squirrel3;

    public function __construct(
        InventoryService $inventoryService, PetRepository $petRepository, PetFactory $petFactory, Squirrel3 $squirrel3,
        EntityManagerInterface $em, MeritRepository $meritRepository, UserStatsRepository $userStatsRepository
    )
    {
        $this->inventoryService = $inventoryService;
        $this->petRepository = $petRepository;
        $this->petFactory = $petFactory;
        $this->em = $em;
        $this->meritRepository = $meritRepository;
        $this->userStatsRepository = $userStatsRepository;
        $this->squirrel3 = $squirrel3;
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
            $startingMerits = array_filter($startingMerits, function($m) use($bonusMerit) {
                return $m !== $bonusMerit->getName();
            });
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
            $harvestedPet->setInDaycare(true);
        }
        else
        {
            $message .= "\n\n" . 'The creature wastes no time in setting up residence in your house.';
            $harvestedPet->setInDaycare(false);
        }

        return $message;
    }
}
