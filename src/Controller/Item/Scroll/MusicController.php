<?php
namespace App\Controller\Item\Scroll;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\UserStatEnum;
use App\Functions\ArrayFunctions;
use App\Repository\UserStatsRepository;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/scroll")
 */
class MusicController extends AbstractController
{
    /**
     * @Route("/music/{inventory}/invoke", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function invokeMusicScroll(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, Squirrel3 $squirrel3,
        UserStatsRepository $userStatsRepository, EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'scroll/music/#/invoke');
        ItemControllerHelpers::validateHouseSpace($inventory, $inventoryService);

        $em->remove($inventory);

        $userStatsRepository->incrementStat($user, UserStatEnum::READ_A_SCROLL);

        $commonItems = [
            'Flute', 'Fiberglass Flute', 'Music Note', 'Gold Triangle'
        ];

        $rareItems = [
            'Bass Guitar', 'Maraca', 'Melodica', 'Sousaphone'
        ];

        $location = $inventory->getLocation();

        $newInventory = [
            $inventoryService->receiveItem('Music Note', $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location),
            $inventoryService->receiveItem($squirrel3->rngNextFromArray($commonItems), $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location),
            $inventoryService->receiveItem($squirrel3->rngNextFromArray($rareItems), $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location),
        ];

        $itemList = array_map(fn(Inventory $i) => $i->getItem()->getName(), $newInventory);
        sort($itemList);

        $em->flush();

        $responseService->addFlashMessage('You read the scroll perfectly, summoning ' . ArrayFunctions::list_nice($itemList) . '.');

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }
}
