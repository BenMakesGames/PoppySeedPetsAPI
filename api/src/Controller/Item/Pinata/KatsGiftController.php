<?php
declare(strict_types=1);

/**
 * This file is part of the Poppy Seed Pets API.
 *
 * The Poppy Seed Pets API is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets API is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets API. If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Controller\Item\Pinata;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Functions\ArrayFunctions;
use App\Functions\EnchantmentRepository;
use App\Functions\SpiceRepository;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/item/katsGift")]
class KatsGiftController
{
    #[Route("/{inventory}/baabbles", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function get14Baabbles(
        Inventory $inventory, InventoryService $inventoryService, EntityManagerInterface $em,
        ResponseService $responseService, UserAccessor $userAccessor, UserStatsService $userStatsService,
        IRandom $rng
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'katsGift/#/baabbles');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $location = $inventory->getLocation();
        $locked = $inventory->getLockedToOwner();

        $em->remove($inventory);

        $inventoryService->receiveItem('Lotus Flower', $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $locked);

        $baabbles = [
            'Black Baabble', 'Black Baabble', 'Black Baabble', 'Black Baabble', 'Black Baabble', 'Black Baabble',
            'White Baabble', 'White Baabble', 'White Baabble', 'White Baabble', 'White Baabble',
            'Gold Baabble', 'Gold Baabble', 'Gold Baabble', 'Gold Baabble',
            'Shiny Baabble', 'Shiny Baabble', 'Shiny Baabble',
        ];

        $fifteenBaabbles = $rng->rngNextSubsetFromArray($baabbles, 14);
        $bleating = SpiceRepository::findOneByName($em, 'Bleating');

        foreach($fifteenBaabbles as $baabble)
        {
            $inventoryService->receiveItem($baabble, $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $locked)
                ->setSpice($bleating)
            ;
        }

        $userStatsService->incrementStat($user, 'Kat\'s Gift Packages Opened');

        $em->flush();

        return $responseService->itemActionSuccess('You open the box, nearly tearing the Lotus flower "bow" in two as you\'re subjected to a sudden barrage of 14 excitedly-bleating baabbles!', [ 'itemDeleted' => true ]);
    }

    #[Route("/{inventory}/67magma", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function get67Magma(
        Inventory $inventory, InventoryService $inventoryService, EntityManagerInterface $em,
        ResponseService $responseService, UserAccessor $userAccessor, UserStatsService $userStatsService
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'katsGift/#/67magma');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $location = $inventory->getLocation();
        $locked = $inventory->getLockedToOwner();

        $em->remove($inventory);

        $inventoryService->receiveItem('Lotus Flower', $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $locked);

        for($i = 0; $i < 67; $i++)
            $inventoryService->receiveItem('Liquid-hot Magma', $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $locked);

        $userStatsService->incrementStat($user, 'Kat\'s Gift Packages Opened');

        $em->flush();

        return $responseService->itemActionSuccess('You open the box, carefully preserving the Lotus flower "bow", and find no less than 67 Liquid-hot Magmas! (Seems like an _oddly-specific_ amount... (And how did those all FIT in there, anyway???))', [ 'itemDeleted' => true ]);
    }

    #[Route("/{inventory}/scrollOfIllusions", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getScrollofIllusions(
        Inventory $inventory, InventoryService $inventoryService, EntityManagerInterface $em,
        ResponseService $responseService, UserAccessor $userAccessor, UserStatsService $userStatsService
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'katsGift/#/scrollOfIllusions');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $location = $inventory->getLocation();
        $locked = $inventory->getLockedToOwner();

        $em->remove($inventory);

        $inventoryService->receiveItem('Lotus Flower', $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $locked);
        $inventoryService->receiveItem('Scroll of Illusions', $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $locked);

        $userStatsService->incrementStat($user, 'Kat\'s Gift Packages Opened');

        $em->flush();

        return $responseService->itemActionSuccess('You open the box, carefully preserving the Lotus flower "bow", and find a Scroll of Illusions inside! (Just what you always wanted! (How did she know?!?))', [ 'itemDeleted' => true ]);
    }

    #[Route("/{inventory}/serum", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getTransmigrationSerum(
        Inventory $inventory, InventoryService $inventoryService, EntityManagerInterface $em,
        ResponseService $responseService, UserAccessor $userAccessor, UserStatsService $userStatsService
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'katsGift/#/serum');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $location = $inventory->getLocation();
        $locked = $inventory->getLockedToOwner();

        $em->remove($inventory);

        $inventoryService->receiveItem('Lotus Flower', $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $locked);
        $inventoryService->receiveItem('½ Species Transmigration Serum', $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $locked);

        $userStatsService->incrementStat($user, 'Kat\'s Gift Packages Opened');

        $em->flush();

        return $responseService->itemActionSuccess('You open the box, carefully preserving the Lotus flower "bow", and find a ½ Species Transmigration Serum inside! (Just what you always wanted! (How did she know?!?))', [ 'itemDeleted' => true ]);
    }

    #[Route("/{inventory}/renamingScrolls", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function get2RenamingScrolls(
        Inventory $inventory, InventoryService $inventoryService, EntityManagerInterface $em,
        ResponseService $responseService, UserAccessor $userAccessor, UserStatsService $userStatsService
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'katsGift/#/renamingScrolls');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $location = $inventory->getLocation();
        $locked = $inventory->getLockedToOwner();

        $em->remove($inventory);

        $inventoryService->receiveItem('Lotus Flower', $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $locked);
        $inventoryService->receiveItem('Renaming Scroll', $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $locked);
        $inventoryService->receiveItem('Renaming Scroll', $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $locked);

        $userStatsService->incrementStat($user, 'Kat\'s Gift Packages Opened');

        $em->flush();

        return $responseService->itemActionSuccess('You open the box, carefully preserving the Lotus flower "bow", and find two Renaming Scrolls inside! (Just what you always wanted! (How did she know?!?))', [ 'itemDeleted' => true ]);
    }
}
