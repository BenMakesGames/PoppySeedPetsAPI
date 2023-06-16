<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\FlavorEnum;
use App\Enum\PetLocationEnum;
use App\Functions\PetColorFunctions;
use App\Repository\MeritRepository;
use App\Repository\PetRepository;
use App\Repository\PetSpeciesRepository;
use App\Service\InventoryService;
use App\Service\PetFactory;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/beeLarva")
 */
class BeeLarvaController extends AbstractController
{
    /**
     * @Route("/{inventory}/hatch", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function hatch(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        InventoryService $inventoryService, PetSpeciesRepository $petSpeciesRepository, Squirrel3 $rng,
        PetFactory $petFactory, MeritRepository $meritRepository, PetRepository $petRepository
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'beeLarva/#/hatch');

        if($inventoryService->loseItem('Royal Jelly', $user, $inventory->getLocation()) < 1)
        {
            return $responseService->itemActionSuccess('Hm... You\'ll need some Royal Jelly to hatch this larva...');
        }

        $em->remove($inventory);

        $em->flush();

        $giantBeeSpecies = $petSpeciesRepository->findOneBy([ 'name' => 'Giant Bee' ]);

        $beeName = $rng->rngNextFromArray([
            'Mellifera', 'Bombus', 'Megachile', 'Eucerini', 'Xylocopa', 'Ceratina', 'Osmia', 'Anthidium',
            'Peponapis', 'Andrena', 'Cineraria', 'Halictus', 'Sphecodes', 'Nomada', 'Eucera', 'Euglossini',
            'Melecta',
        ]);

        $petColors = PetColorFunctions::generateRandomPetColors($rng);

        $newPet = $petFactory->createPet(
            $user, $beeName, $giantBeeSpecies,
            $petColors[0], $petColors[1],
            FlavorEnum::getRandomValue($rng),
            $meritRepository->getRandomStartingMerit()
        );

        $newPet
            ->increaseLove(10)
            ->increaseSafety(10)
            ->increaseEsteem(10)
            ->increaseFood(-8)
            ->setScale($rng->rngNextInt(80, 120))
        ;

        $message = 'The larva unfurls itself, and molts, revealing a beautiful little bee!';

        $numberOfPetsAtHome = $petRepository->getNumberAtHome($user);

        $petJoinsHouse = $numberOfPetsAtHome < $user->getMaxPets();

        if(!$petJoinsHouse)
        {
            $newPet->setLocation(PetLocationEnum::DAYCARE);
            $message .= "\n\nYour house is full, so it flies off to the daycare.";
        }

        $em->flush();

        $responseService
            ->setReloadPets($petJoinsHouse)
            ->setReloadInventory(true)
        ;

        return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);
    }

    /**
     * @Route("/{inventory}/returnToBeehive", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function returnToBeehive(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'beeLarva/#/returnToBeehive');

        if(!$user->getBeehive())
            return $responseService->itemActionSuccess('Hey, that\'s spoilers! You don\'t have the... thing you need... to be able to do that! Yet!');

        $user->getBeehive()
            ->setWorkers($user->getBeehive()->getWorkers() + 1)
            ->setFlowerPower(36)
            ->setInteractionPower()
        ;

        $em->remove($inventory);
        $em->flush();

        return $responseService->itemActionSuccess('You return the larva to Queen ' . $user->getBeehive()->getQueenName() . ', who thanks you for your honor and loyalty. The colony redoubles their efforts, and hey: with 1 more worker than before! (Every bee counts!)', [ 'itemDeleted' => true ]);
    }

    /**
     * @Route("/{inventory}/giveToAntQueen", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function giveToAntQueen(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        InventoryService $inventoryService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'beeLarva/#/giveToAntQueen');

        if($inventoryService->loseItem('Ant Queen', $user, $inventory->getLocation()) < 1)
            return $responseService->itemActionSuccess('Narrator: But there\'s was no Ant Queen for ' . $user->getName() . ' to give it to.');

        $inventoryService->receiveItem('Ant Queen\'s Favor', $user, $user, $user->getName() . ' received this from an Ant Queen in exchange for a Bee Larva...', $inventory->getLocation());

        $em->remove($inventory);
        $em->flush();

        $responseService->setReloadInventory(true);

        return $responseService->itemActionSuccess('The Ant Queen thanks you for your honor and loyalty, vows to repay the favor, and departs with the larva. (You got an Ant Queen\'s Favor!)', [ 'itemDeleted' => true ]);
    }
}
