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
use App\Enum\UserStat;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\UserAccessor;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/brick")]
class ClayBrick
{
    #[Route("/{inventory}/addToFireplace", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function addToFireplace(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, IRandom $rng,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'brick/#/addToFireplace');

        if(!$user->getFireplace())
            return $responseService->itemActionSuccess('Hm? Fireplace? What Fireplace? You don\'t have a Fireplace.');

        $user->getFireplace()->addBrick();

        $em->remove($inventory);

        $em->flush();

        $soHandy = $rng->rngNextFromArray([
            '👩‍🍳👌',
            'It\'s perfect.',
            'God _damn_, that\'s a good looking Fireplace!',
            'You\'re so handi! Er, handy. (Spelling is weird.)',
            'Beautiful.',
            'All that practice playing Carcassonne has really paid off!',
        ]);

        return $responseService->itemActionSuccess("You slot the brick into an empty spot in the Fireplace, then take a step back to admire your handiwork.\n\n$soHandy", [ 'itemDeleted' => true ]);
    }
}