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
use App\Entity\Vault;
use App\Enum\PetSkillEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Functions\InventoryHelpers;
use App\Functions\UserUnlockedFeatureHelpers;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use App\Service\UserAccessor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item")]
class InfinityVaultBlueprintController
{
    #[Route("/infinityVaultBlueprint/{inventory}", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function buildInfinityVault(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        PetExperienceService $petExperienceService, UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'infinityVaultBlueprint');

        if($user->hasUnlockedFeature(UnlockableFeatureEnum::InfinityVault))
            throw new PSPInvalidOperationException('You\'ve already got an Infinity Vault!');

        $quintessence = InventoryHelpers::findOneToConsume($em, $user, 'Quintessence');

        if(!$quintessence)
            return $responseService->itemActionSuccess('You\'ll need a Quintessence to build an Infinity Vault.');

        $pet = BlueprintHelpers::getPet($em, $user, $request);

        $em->remove($inventory);
        $em->remove($quintessence);

        UserUnlockedFeatureHelpers::create($em, $user, UnlockableFeatureEnum::InfinityVault);

        $vault = new Vault($user);
        $em->persist($vault);
        $user->setVault($vault);

        BlueprintHelpers::rewardHelper(
            $petExperienceService, $responseService, $em,
            $pet,
            PetSkillEnum::Arcana,
            'The blueprint crackles with arcane energy as ' . $pet->getName() . ' helps you piece it together. A shimmer of infinity fills the air, and - just like that - the vault appears! ("Infinity Vault" has been added to the menu!)',
            $pet->getName() . ' built an Infinity Vault with ' . $user->getName() . '!'
        );

        $em->flush();

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }
}
