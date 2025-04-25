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
use App\Enum\UnlockableFeatureEnum;
use App\Functions\UserUnlockedFeatureHelpers;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\UserAccessor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[Route("/item/cookingBuddy")]
class CookingBuddy
{
    #[Route("/{inventory}/addOrReplace", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function addOrReplace(
        Inventory $inventory, EntityManagerInterface $em, ResponseService $responseService,
        IRandom $rng, UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'cookingBuddy/#/addOrReplace');

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::CookingBuddy))
            UserUnlockedFeatureHelpers::create($em, $user, UnlockableFeatureEnum::CookingBuddy);

        if($user->getCookingBuddy())
        {
            if($user->getCookingBuddy()->getAppearance() === $inventory->getItem()->getImage())
            {
                $user->getCookingBuddy()->generateNewName($rng);

                $em->remove($inventory);
                $em->flush();

                return $responseService->itemActionSuccess('Your new Cooking Buddy is named "' . $user->getCookingBuddy()->getName() . '".', [ 'itemDeleted' => true ]);
            }
            else
            {
                $user->getCookingBuddy()->setAppearance($inventory->getItem()->getImage());

                $em->remove($inventory);
                $em->flush();

                return $responseService->itemActionSuccess('Your Cooking Buddy\'s appearance has been changed!', [ 'itemDeleted' => true ]);
            }
        }
        else
        {
            $responseText = 'You plug the ' . $inventory->getItem()->getName() . ' into an outlet in your kitchen, and it springs to life! (The Cooking Buddy has been added to your menu!)';

            $cookingBuddy = (new \App\Entity\CookingBuddy())
                ->setOwner($user)
                ->setAppearance($inventory->getItem()->getImage())
                ->generateNewName($rng);

            $em->persist($cookingBuddy);
            $em->remove($inventory);

            $em->flush();

            return $responseService->itemActionSuccess($responseText, [ 'itemDeleted' => true ]);
        }
    }
}