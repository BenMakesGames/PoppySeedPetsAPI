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


namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Entity\MuseumItem;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPInvalidOperationException;
use App\Functions\ItemRepository;
use App\Functions\UserQuestRepository;
use App\Service\MuseumService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/item")]
class WunderbussController
{
    #[Route("/wunderbuss/{inventory}/usedWish", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function usedWish(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'wunderbuss');

        $usedAWunderbuss = UserQuestRepository::findOrCreate($em, $user, 'Used a Wunderbuss', false);

        return $responseService->success($usedAWunderbuss->getValue());
    }

    #[Route("/wunderbuss/{inventory}", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function search(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        MuseumService $museumService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'wunderbuss');

        $usedAWunderbuss = UserQuestRepository::findOrCreate($em, $user, 'Used a Wunderbuss', false);

        if($usedAWunderbuss->getValue())
            throw new PSPInvalidOperationException('You\'ve already wished for something from the Wunderbuss. (You only get one wish, unfortunately...)');

        $searchForId = $request->request->getInt('itemId');

        if($searchForId <= 0)
            throw new PSPFormValidationException('An item to search for must be selected!');

        $itemToFind = ItemRepository::findOneById($em, $searchForId);

        $donatedItem = $em->getRepository(MuseumItem::class)->findOneBy([
            'item' => $itemToFind,
            'user' => $user,
        ]);

        if($donatedItem)
            throw new PSPInvalidOperationException('You\'ve already donated ' . $itemToFind->getNameWithArticle() . '.');

        // 1. donate the item
        $museumService->forceDonateItem($user, $itemToFind, 'This item was created by wishing for it from Wunderboss!');

        // 2. count the wish as granted
        $usedAWunderbuss->setValue(true);

        // 3. it is done
        $em->flush();

        $responseService->setReloadInventory(true);
        $responseService->addFlashMessage('IT IS DONE! Olfert\'s spirit thanks you, as do I!');

        return $responseService->success();
    }

}
