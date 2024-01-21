<?php
namespace App\Controller\Item\Pinata;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\ItemGroup;
use App\Entity\User;
use App\Functions\ArrayFunctions;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/boosterPack")]
class BoosterPackController extends AbstractController
{
    private const SET_ITEM_GROUP_NAMES = [
        'one' => 'Hollow Earth Booster Pack',
        'two' => 'Community Booster Pack',
    ];

    #[Route("/{set}/{inventory}/open", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function openBoosterPackOne(
        string $set, Inventory $inventory,

        ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, IRandom $rng
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'boosterPack/' . $set . '/#/open');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $location = $inventory->getLocation();

        $itemGroupNameBase = self::SET_ITEM_GROUP_NAMES[$set];

        $commons = $em->getRepository(ItemGroup::class)->findOneBy([ 'name' => $itemGroupNameBase . ': Common' ]);
        $uncommons = $em->getRepository(ItemGroup::class)->findOneBy([ 'name' => $itemGroupNameBase . ': Uncommon' ]);
        $rares = $em->getRepository(ItemGroup::class)->findOneBy([ 'name' => $itemGroupNameBase . ': Rare' ]);

        $tiles = [
            InventoryService::getRandomItemFromItemGroup($rng, $commons),
            InventoryService::getRandomItemFromItemGroup($rng, $commons),
            InventoryService::getRandomItemFromItemGroup($rng, $uncommons),
            InventoryService::getRandomItemFromItemGroup($rng, $rares)
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
