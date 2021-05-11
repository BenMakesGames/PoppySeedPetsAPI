<?php
namespace App\Controller\Item\Pinata;

use App\Controller\Item\PoppySeedPetsItemController;
use App\Entity\Inventory;
use App\Functions\ArrayFunctions;
use App\Repository\ItemGroupRepository;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/boosterPack")
 */
class BoosterPackController extends PoppySeedPetsItemController
{
    /**
     * @Route("/one/{inventory}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function openBoosterPackOne(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, ItemGroupRepository $itemGroupRepository
    )
    {
        $this->validateInventory($inventory, 'boosterPack/one/#/open');
        $this->validateHouseSpace($inventory, $inventoryService);

        $user = $this->getUser();
        $location = $inventory->getLocation();

        $commons = $itemGroupRepository->findOneByName('Hollow Earth Booster Pack: Common');
        $uncommons = $itemGroupRepository->findOneByName('Hollow Earth Booster Pack: Uncommon');
        $rares = $itemGroupRepository->findOneByName('Hollow Earth Booster Pack: Rare');

        $tiles = [
            $inventoryService->getRandomItemFromItemGroup($commons),
            $inventoryService->getRandomItemFromItemGroup($commons),
            $inventoryService->getRandomItemFromItemGroup($uncommons),
            $inventoryService->getRandomItemFromItemGroup($rares)
        ];

        $tileNames = [
            $tiles[0]->getName() . ' (☆)',
            $tiles[1]->getName() . ' (☆)',
            $tiles[2]->getName() . ' (☆☆)',
            $tiles[3]->getName() . ' (☆☆☆)',
        ];

        foreach($tiles as $tile)
            $inventoryService->receiveItem($tile, $user, $user, $user->getName() . ' found this in ' . $inventory->getItem()->getNameWithArticle() . '.', $location);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('You open the ' . $inventory->getItem()->getName() . ', receiving ' . ArrayFunctions::list_nice($tileNames) . '!', [ 'itemDeleted' => true ]);
    }
}
