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
use App\Exceptions\PSPNotUnlockedException;
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
class MoondialBlueprintController
{
    #[Route("/moondialBlueprint/{inventory}", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function buildBirdBath(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        PetExperienceService $petExperienceService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'moondialBlueprint');

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Greenhouse))
            throw new PSPNotUnlockedException('Greenhouse');

        $greenhouse = $user->getGreenhouse()
            ?? throw new PSPNotUnlockedException('Greenhouse');

        if($greenhouse->hasMoondial())
            return $responseService->error(200, [ 'Your Greenhouse already has a Moondial!' ]);

        $pet = BlueprintHelpers::getPet($em, $user, $request);

        $blackonite = InventoryHelpers::findOneToConsume($em, $user, 'Blackonite');
        $rock = InventoryHelpers::findOneToConsume($em, $user, 'Rock');

        if(!$blackonite || !$rock)
            return $responseService->error(422, [ 'Hm... you\'re going to need a Rock, and some Blackonite to make this...' ]);

        $em->remove($blackonite);
        $em->remove($rock);
        $em->remove($inventory);

        $greenhouse->setHasMoondial(true);

        $flashMessage = 'You build a Moondial with ' . $pet->getName() . '!';

        BlueprintHelpers::rewardHelper(
            $petExperienceService, $responseService, $em,
            $pet,
            PetSkillEnum::Crafts,
            $flashMessage,
            $pet->getName() . ' built a Moondial in the Greenhouse with ' . $user->getName() . '!'
        );

        $em->flush();

        return $responseService->itemActionSuccess(
            null,
            [ 'itemDeleted' => true ]
        );
    }
}
