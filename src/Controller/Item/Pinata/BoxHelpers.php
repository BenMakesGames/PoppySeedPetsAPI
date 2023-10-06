<?php

namespace App\Controller\Item\Pinata;

use App\Entity\Inventory;
use App\Entity\User;
use App\Functions\ArrayFunctions;
use App\Functions\InventoryModifierFunctions;
use App\Repository\UserStatsRepository;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class BoxHelpers
{
    /**
     * @param Inventory[] $newInventory
     */
    public static function countRemoveFlushAndRespond(
        string $messagePrefix, User $user, Inventory $inventory, array $newInventory,
        ResponseService $responseService, EntityManagerInterface $em
    ): JsonResponse
    {
        UserStatsRepository::incrementStat($em, $user, 'Opened ' . $inventory->getItem()->getNameWithArticle());

        $itemList = array_map(fn(Inventory $i) => InventoryModifierFunctions::getNameWithModifiers($i), $newInventory);
        sort($itemList);

        $em->remove($inventory);

        $em->flush();

        $responseService->setReloadInventory();

        return $responseService->itemActionSuccess($messagePrefix . ' ' . ArrayFunctions::list_nice($itemList) . '.', [ 'itemDeleted' => true ]);
    }
}