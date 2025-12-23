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

namespace App\Controller\Item\Blueprint;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Enum\PetSkillEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Functions\InventoryHelpers;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use App\Service\UserAccessor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item")]
class ForgeBlueprintController
{
    #[Route("/forgeBlueprint/{inventory}", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function buildForge(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        PetExperienceService $petExperienceService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'forgeBlueprint');

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Fireplace))
            return $responseService->error(400, [ 'You need a Fireplace to build a Forge!' ]);

        $fireplace = $user->getFireplace()
            ?? throw new PSPInvalidOperationException("You don't have a Greenhouse!");

        if($fireplace->getHasForge())
            return $responseService->error(200, [ 'Your Fireplace already has a Forge!' ]);

        $pet = BlueprintHelpers::getPet($em, $user, $request);

        $ironBar = InventoryHelpers::findOneToConsume($em, $user, 'Iron Bar');
        $heavyHammer = InventoryHelpers::findOneToConsume($em, $user, 'Heavy Hammer');

        if(!$ironBar || !$heavyHammer)
            return $responseService->error(422, [ 'Hm... you\'re going to need an Iron Bar AND a Heavy Hammer to make this...' ]);

        $em->remove($ironBar);
        $em->remove($heavyHammer);
        $em->remove($inventory);

        $fireplace->setHasForge(true);

        $flashMessage = 'You build a Forge with ' . $pet->getName() . '!';

        BlueprintHelpers::rewardHelper(
            $petExperienceService, $responseService, $em,
            $pet,
            PetSkillEnum::Crafts,
            $flashMessage,
            $pet->getName() . ' built a Forge for the Fireplace with ' . $user->getName() . '!'
        );

        $em->flush();

        return $responseService->itemActionSuccess(
            null,
            [ 'itemDeleted' => true ]
        );
    }
}
