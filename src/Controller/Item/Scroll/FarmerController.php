<?php
declare(strict_types=1);

namespace App\Controller\Item\Scroll;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\UserStatEnum;
use App\Functions\ArrayFunctions;
use App\Functions\DateFunctions;
use App\Functions\UserQuestRepository;
use App\Service\Clock;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/scroll")]
class FarmerController extends AbstractController
{
    #[Route("/farmers/{inventory}/invoke", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function invokeFarmerScroll(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        UserStatsService $userStatsRepository, EntityManagerInterface $em, Clock $clock
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'scroll/farmers/#/invoke');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $wheatOrCorn = DateFunctions::isCornMoon($clock->now) ? 'Corn' : 'Wheat';

        $em->remove($inventory);

        $userStatsRepository->incrementStat($user, UserStatEnum::READ_A_SCROLL);

        if($user->getGreenhouse())
        {
            $expandedGreenhouseWithFarmerScroll = UserQuestRepository::findOrCreate($em, $user, 'Expanded Greenhouse with Farmer Scroll', false);

            if(!$expandedGreenhouseWithFarmerScroll->getValue())
            {
                $expandedGreenhouseWithFarmerScroll->setValue(true);

                $user->getGreenhouse()->increaseMaxPlants(1);

                $em->flush();

                return $responseService->itemActionSuccess('You read the scroll; another plot of space in your Greenhouse appears, as if by magic! In fact, thinking about it, it was _100%_ by magic!', [ 'itemDeleted' => true ]);
            }
        }

        $items = [
            'Straw Hat', $wheatOrCorn, 'Scythe', 'Creamy Milk', 'Egg', 'Grandparoot', 'Crooked Stick', 'Potato'
        ];

        $newInventory = [];
        $location = $inventory->getLocation();

        foreach($items as $item)
            $newInventory[] = $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location);

        $itemList = array_map(fn(Inventory $i) => $i->getItem()->getName(), $newInventory);
        sort($itemList);

        $em->flush();

        $responseService->addFlashMessage('You read the scroll, summoning ' . ArrayFunctions::list_nice($itemList) . '.');

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }
}
