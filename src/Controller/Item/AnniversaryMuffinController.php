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
use App\Entity\User;
use App\Functions\ItemRepository;
use App\Service\ResponseService;
use App\Service\TransactionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/item/anniversaryMuffin")]
class AnniversaryMuffinController
{
    #[Route("/{inventory}/lengthySkillScroll", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function wishForLengthySkillScroll(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'anniversaryMuffin/#/lengthySkillScroll');

        $inventory
            ->changeItem(ItemRepository::findOneByName($em, 'Lengthy Scroll of Skill'))
            ->setModifiedOn()
        ;

        $em->flush();

        $responseService->setReloadInventory(true);

        return $responseService->itemActionSuccess('You make a wish and blow out the candle... and the muffin twists itself into a Lengthy Scroll of Skill! (What was _in_ that muffin?!)', [ 'itemDeleted' => true ]);
    }

    #[Route("/{inventory}/museumFavor", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function wishFor700MuseumFavor(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        TransactionService $transactionService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'anniversaryMuffin/#/museumFavor');

        $transactionService->getMuseumFavor($user, 700, 'You wished for 700 Museum Favor on a muffin! (What _is_ this game??)');

        $em->remove($inventory);
        $em->flush();

        return $responseService->itemActionSuccess('You make a wish and blow out the candle... and the muffin vanishes, somehow granting you with 700 Favor in the process! (What was _in_ that muffin?!)', [ 'itemDeleted' => true ]);
    }
}
