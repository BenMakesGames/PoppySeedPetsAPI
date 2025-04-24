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


namespace App\Controller\Item\Book;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\UnlockableFeatureEnum;
use App\Functions\UserUnlockedFeatureHelpers;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/starKindred")]
class StarKindredController extends AbstractController
{
    #[Route("/{inventory}/read", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function read(
        Inventory $inventory, ResponseService $responseService,
        EntityManagerInterface $em
    ): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'starKindred/#/read');

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::StarKindred))
        {
            UserUnlockedFeatureHelpers::create($em, $user, UnlockableFeatureEnum::StarKindred);

            return $responseService->itemActionSuccess('Looks like a game you can play with your pets! You study the book several times, memorizing every detail... (You can now play ★Kindred with your pets! Find it in the menu!)');
        }
        else
        {
            return $responseService->itemActionSuccess('You already read this book front-to-back several times... there is nothing more to learn from it.');
        }
    }
}