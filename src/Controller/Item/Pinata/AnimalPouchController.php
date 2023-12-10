<?php
namespace App\Controller\Item\Pinata;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Functions\ArrayFunctions;
use App\Functions\EnchantmentRepository;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/animalPouch")]
class AnimalPouchController extends AbstractController
{
    #[Route("/magpie/{inventory}/open", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function openMagpiePouch(
        Inventory $inventory, InventoryService $inventoryService, EntityManagerInterface $em,
        ResponseService $responseService, IRandom $squirrel3
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'animalPouch/magpie/#/open');

        $possibleItems = [
            'Fool\'s Spice',
            $squirrel3->rngNextFromArray([ '"Gold" Idol', 'Phishing Rod' ]),
            $squirrel3->rngNextFromArray([ 'Glass', 'Crystal Ball' ]),
            'Mixed Nuts',
            $squirrel3->rngNextFromArray([ 'Fluff', 'String' ]),
            'Sand Dollar'
        ];

        $location = $inventory->getLocation();
        $locked = $inventory->getLockedToOwner();

        $em->remove($inventory);

        $listOfItems = $squirrel3->rngNextSubsetFromArray($possibleItems, 3);

        foreach($listOfItems as $itemName)
        {
            $item = $inventoryService->receiveItem($itemName, $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $locked);

            if($itemName === 'Phishing Rod')
                $item->setEnchantment(EnchantmentRepository::findOneByName($em, 'Clinquant'));
        }

        $em->flush();

        return $responseService->itemActionSuccess('You open the pouch, revealing ' . ArrayFunctions::list_nice($listOfItems) . '!', [ 'itemDeleted' => true ]);
    }

    #[Route("/raccoon/{inventory}/open", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function openRaccoonPouch(
        Inventory $inventory, InventoryService $inventoryService, EntityManagerInterface $em,
        ResponseService $responseService, IRandom $squirrel3
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'animalPouch/raccoon/#/open');

        $possibleItems = [
            'Beans',
            $squirrel3->rngNextFromArray([ 'Baked Fish Fingers', 'Deep-fried Toad Legs' ]),
            'Trout Yogurt',
            'Caramel-covered Popcorn',
            $squirrel3->rngNextFromArray([ 'Instant Ramen (Dry)', 'Paper Bag' ]),
            $squirrel3->rngNextFromArray([ 'Mixed Nut Brittle', 'Berry Muffin' ]),
        ];

        $spice = $inventory->getSpice();
        $location = $inventory->getLocation();
        $locked = $inventory->getLockedToOwner();

        $em->remove($inventory);

        $listOfItems = $squirrel3->rngNextSubsetFromArray($possibleItems, 3);

        foreach($listOfItems as $itemName)
        {
            $item = $inventoryService->receiveItem($itemName, $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $locked);

            if($spice)
            {
                $item->setSpice($spice);
                $spice = null;
            }
        }

        $em->flush();

        if($spice)
            return $responseService->itemActionSuccess('You open the pouch, revealing ' . ArrayFunctions::list_nice($listOfItems) . ' - and they\'re all so ' . $spice->getName() . '!', [ 'itemDeleted' => true ]);
        else
            return $responseService->itemActionSuccess('You open the pouch, revealing ' . ArrayFunctions::list_nice($listOfItems) . '!', [ 'itemDeleted' => true ]);
    }
}
