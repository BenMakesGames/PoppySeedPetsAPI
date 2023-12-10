<?php
namespace App\Controller\Item\Pinata;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\UserStatEnum;
use App\Functions\ItemRepository;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/burntLog")]
class BurntLogController extends AbstractController
{
    #[Route("/{inventory}/break", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function openBurntLog(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, IRandom $squirrel3,
        EntityManagerInterface $em, UserStatsService $userStatsRepository
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'burntLog/#/break');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $location = $inventory->getLocation();
        $lockedToOwner = $inventory->getLockedToOwner();

        $stat = $userStatsRepository->incrementStat($user, UserStatEnum::BURNT_LOGS_BROKEN);

        $extraItem = ItemRepository::findOneByName($em, $squirrel3->rngNextFromArray([
            'Crooked Stick',
            'Iron Ore',
            'Glass',
            'Glowing Six-sided Die',
            'Fried Egg',
        ]));

        if($squirrel3->rngNextInt(1, 4) === 1)
        {
            $charcoalReceived = 'Charcoal, Liquid-hot Magma';
            $inventoryService->receiveItem('Liquid-hot Magma', $user, $user, $user->getName() . ' pulled this out of a Burnt Log.', $location, $lockedToOwner);
            $inventoryService->receiveItem('Charcoal', $user, $user, $user->getName() . ' pulled this out of a Burnt Log.', $location, $lockedToOwner);
        }
        else
        {
            $charcoalReceived = 'three Charcoal';
            $inventoryService->receiveItem('Charcoal', $user, $user, $user->getName() . ' pulled this out of a Burnt Log.', $location, $lockedToOwner);
            $inventoryService->receiveItem('Charcoal', $user, $user, $user->getName() . ' pulled this out of a Burnt Log.', $location, $lockedToOwner);
            $inventoryService->receiveItem('Charcoal', $user, $user, $user->getName() . ' pulled this out of a Burnt Log.', $location, $lockedToOwner);
        }

        if(($stat->getValue() + 6) % 10 === 0)
        {
            $charcoalReceived .= ', a Letter from the Library of Fire';
            $inventoryService->receiveItem('Letter from the Library of Fire', $user, $user, $user->getName() . ' pulled this out of a Burnt Log.', $location, $lockedToOwner);
        }

        $inventoryService->receiveItem($extraItem, $user, $user, $user->getName() . ' pulled this out of a Burnt Log.', $location, $lockedToOwner);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('You break the Burnt Log apart, receiving ' . $charcoalReceived . ', and ' . $extraItem->getNameWithArticle() . '!', [ 'itemDeleted' => true ]);
    }
}
