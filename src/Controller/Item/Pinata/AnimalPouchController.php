<?php
namespace App\Controller\Item\Pinata;

use App\Controller\Item\PoppySeedPetsItemController;
use App\Entity\Inventory;
use App\Functions\ArrayFunctions;
use App\Model\ItemQuantity;
use App\Repository\EnchantmentRepository;
use App\Repository\ItemRepository;
use App\Service\InventoryService;
use App\Service\PetRelationshipService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/animalPouch")
 */
class AnimalPouchController extends PoppySeedPetsItemController
{
    /**
     * @Route("/magpie/{inventory}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function openMagpiePouch(
        Inventory $inventory, InventoryService $inventoryService, EntityManagerInterface $em,
        ResponseService $responseService, EnchantmentRepository $enchantmentRepository
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'animalPouch/magpie/#/open');

        $possibleItems = [
            'Fool\'s Spice',
            ArrayFunctions::pick_one([ '"Gold" Idol', 'Phishing Rod' ]),
            ArrayFunctions::pick_one([ 'Glass', 'Crystal Ball' ]),
            'Mixed Nuts',
            ArrayFunctions::pick_one([ 'Fluff', 'String' ]),
            'Sand Dollar'
        ];

        $location = $inventory->getLocation();
        $locked = $inventory->getLockedToOwner();

        $em->remove($inventory);

        $listOfItems = ArrayFunctions::pick_some($possibleItems, 3);

        foreach($listOfItems as $itemName)
        {
            $item = $inventoryService->receiveItem($itemName, $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $locked);

            if($itemName === 'Phishing Rod')
                $item->setEnchantment($enchantmentRepository->findOneByName('Moneyed'));
        }

        $em->flush();

        return $responseService->itemActionSuccess('You open the pouch, revealing ' . ArrayFunctions::list_nice($listOfItems) . '!', [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }

    /**
     * @Route("/raccoon/{inventory}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function openRaccoonPouch(
        Inventory $inventory, InventoryService $inventoryService, EntityManagerInterface $em,
        ResponseService $responseService
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'animalPouch/raccoon/#/open');

        $possibleItems = [
            'Beans',
            ArrayFunctions::pick_one([ 'Baked Fish Fingers', 'Deep-fried Toad Legs' ]),
            'Trout Yogurt',
            'Caramel-covered Popcorn',
            ArrayFunctions::pick_one([ 'Instant Ramen (Dry)', 'Paper Bag' ]),
            ArrayFunctions::pick_one([ 'Mixed Nut Brittle', 'Berry Muffin' ]),
        ];

        $spice = $inventory->getSpice();
        $location = $inventory->getLocation();
        $locked = $inventory->getLockedToOwner();

        $em->remove($inventory);

        $listOfItems = ArrayFunctions::pick_some($possibleItems, 3);

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
            return $responseService->itemActionSuccess('You open the pouch, revealing ' . ArrayFunctions::list_nice($listOfItems) . ' - and they\'re all so ' . $spice->getName() . '!', [ 'reloadInventory' => true, 'itemDeleted' => true ]);
        else
            return $responseService->itemActionSuccess('You open the pouch, revealing ' . ArrayFunctions::list_nice($listOfItems) . '!', [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }
}
