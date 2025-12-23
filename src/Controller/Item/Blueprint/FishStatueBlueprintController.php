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
use App\Enum\LocationEnum;
use App\Enum\PetSkillEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Functions\InventoryHelpers;
use App\Functions\ItemRepository;
use App\Service\InventoryService;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use App\Service\UserAccessor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item")]
class FishStatueBlueprintController
{
    #[Route("/fishStatue/{inventory}", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function installFishStatue(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        PetExperienceService $petExperienceService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'fishStatue');

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Greenhouse))
            return $responseService->error(400, [ 'You need a Greenhouse to install a Fish Statue!' ]);

        $greenhouse = $user->getGreenhouse()
            ?? throw new PSPInvalidOperationException("You don't have a Greenhouse!");

        if($greenhouse->isHasFishStatue())
            return $responseService->error(200, [ 'Your Greenhouse already has a Fish State!' ]);

        $pet = BlueprintHelpers::getPet($em, $user, $request);

        $threeDeePrinterId = ItemRepository::getIdByName($em, '3D Printer');

        if(InventoryService::countInventory($em, $user->getId(), $threeDeePrinterId, LocationEnum::Home) < 1)
            return $responseService->itemActionSuccess('The statue appears to be a fountain! You and ' . $pet->getName() . ' are going to need a 3D Printer at home, and some Plastic to make some pipes...');

        $plastic = InventoryHelpers::findOneToConsume($em, $user, 'Plastic');

        if(!$plastic)
            return $responseService->itemActionSuccess('The statue appears to be a fountain! You and ' . $pet->getName() . ' are going to need a 3D Printer at home, and some Plastic to make some pipes...');

        $em->remove($plastic);
        $em->remove($inventory);

        $greenhouse->setHasFishStatue(true);

        $flashMessage = 'You install a Fish Statue with ' . $pet->getName() . '!';

        BlueprintHelpers::rewardHelper(
            $petExperienceService, $responseService, $em,
            $pet,
            PetSkillEnum::Crafts,
            $flashMessage,
            $pet->getName() . ' installed a Fish Statue in the Greenhouse with ' . $user->getName() . '!'
        );

        $em->flush();

        return $responseService->itemActionSuccess(
            null,
            [ 'itemDeleted' => true ]
        );
    }
}
