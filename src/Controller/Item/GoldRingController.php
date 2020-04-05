<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Entity\Pet;
use App\Entity\PetSkills;
use App\Enum\FlavorEnum;
use App\Functions\ArrayFunctions;
use App\Functions\ColorFunctions;
use App\Repository\ItemRepository;
use App\Repository\MeritRepository;
use App\Repository\PetRepository;
use App\Repository\PetSpeciesRepository;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/goldRing")
 */
class GoldRingController extends PoppySeedPetsItemController
{
    /**
     * @Route("/{inventory}/smash", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function smash(
        Inventory $inventory, ResponseService $responseService, ItemRepository $itemRepository,
        EntityManagerInterface $em
    )
    {
        $this->validateInventory($inventory, 'goldRing/#/smash');

        $inventory->changeItem($itemRepository->findOneByName('Gold Bar'));

        $message = ArrayFunctions::pick_one([
            'Easy as 1, 2, 3.',
            'Easy as pie.',
            'Easy as falling off a log.',
            'Simple as A, B, C.',
            'No sweat.',
            'Like shooting fish in a barrel.',
            'Child\'s play.',
            'You could do this with one hand behind your back. So you do. OH GOD, IT\'S REALLY HAR--nah, it\'s still easy.',
            'Piece of cake.',
            'A task as pleasurable as it is simple.',
            'A breeze.',
            'Easy-peasy.',
            'Elementary.',
            'It\'s a cakewalk. (You know: when you take your cake out for a walk? I guess? Maybe? Okay, smarty pants, _you_ tell _me_ what a cakewalk is, then!)',
            'It\' a cinch!',
            'No problem.',
            'You\'ve scarcely done anything simpler!',
            'A walk in the park.',
        ]);

        $em->flush();

        return $responseService->itemActionSuccess($message, [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }

    /**
     * @Route("/{inventory}/collect100", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function collect100(
        Inventory $inventory, EntityManagerInterface $em, InventoryService $inventoryService, ResponseService $responseService,
        PetSpeciesRepository $petSpeciesRepository, MeritRepository $meritRepository, PetRepository $petRepository
    )
    {
        $this->validateInventory($inventory, 'goldRing/#/collect100');

        $goldRingItem = $inventory->getItem();

        $user = $this->getUser();

        $count = $inventoryService->countInventory($user, $goldRingItem, $inventory->getLocation());

        if($count < 20)
        {
            return $responseService->itemActionSuccess('I\'m only counting ' . $count . ', so...');
        }
        else if($count < 45)
        {
            return $responseService->itemActionSuccess('Up to ' . $count . '! Not bad! Still a ways to go, though!');
        }
        else if($count < 60)
        {
            return $responseService->itemActionSuccess($count . '! About half-way there!');
        }
        else if($count < 80)
        {
            return $responseService->itemActionSuccess('Dang, ' . $count . '?! You\'re really serious about this!');
        }
        else if($count < 95)
        {
            return $responseService->itemActionSuccess('omg! ' . $count . '!');
        }
        else if($count == 95)
            return $responseService->itemActionSuccess($count . '!');
        else if($count == 96)
            return $responseService->itemActionSuccess($count . '!!');
        else if($count == 97)
            return $responseService->itemActionSuccess($count . '!! Just 3 more!');
        else if($count == 98)
            return $responseService->itemActionSuccess($count . '!! So close!');
        else if($count == 99)
            return $responseService->itemActionSuccess($count . '!! AAAAAAAAAAAAAA!!');
        else
        {
            $inventoryService->loseItem($goldRingItem, $user, $inventory->getLocation(), 100);

            $hedgehog = $petSpeciesRepository->findOneBy([ 'name' => 'Hedgehog' ]);

            $petSkills = new PetSkills();

            $em->persist($petSkills);

            $newPet = (new Pet())
                ->setSpecies($hedgehog)
                ->setFavoriteFlavor(FlavorEnum::getRandomValue())
                ->setOwner($user)
                ->setName(ArrayFunctions::pick_one([
                    'Speedy', 'Dash', 'Blur', 'Quicker',
                ]))
                ->increaseLove(10)
                ->increaseSafety(10)
                ->increaseEsteem(10)
                ->increaseFood(-8)
                ->setSkills($petSkills)
                ->addMerit($meritRepository->getRandomStartingMerit())
            ;

            $em->persist($newPet);

            $message = '100 Gold Rings!!! That\'s one extra Hedgehog!';

            $numberOfPetsAtHome = $petRepository->getNumberAtHome($user);

            $petJoinsHouse = $numberOfPetsAtHome < $user->getMaxPets();

            if(!$petJoinsHouse)
            {
                $newPet->setInDaycare(true);
                $message .= "\n\nYour house is full, so it dashes off the daycare.";
            }

            $petColors = ColorFunctions::generateRandomPetColors();
            $newPet
                ->setColorA($petColors[0])
                ->setColorB($petColors[1]);
            ;

            $em->flush();

            return $responseService->itemActionSuccess($message, [ 'reloadInventory' => true, 'itemDeleted' => true, 'reloadPets' => $petJoinsHouse ]);
        }
    }
}
