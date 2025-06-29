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
use App\Functions\UserUnlockedFeatureHelpers;
use App\Service\BeehiveService;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use App\Service\UserAccessor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item")]
class BeehiveBlueprintController
{
    #[Route("/beehiveBlueprint/{inventory}", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function buildBeehive(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        BeehiveService $beehiveService, PetExperienceService $petExperienceService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'beehiveBlueprint');

        if($user->hasUnlockedFeature(UnlockableFeatureEnum::Beehive))
            throw new PSPInvalidOperationException('You\'ve already got a Beehive!');

        $magnifyingGlass = InventoryHelpers::findAnyOneFromItemGroup($em, $user, 'Magnifying Glass', [
            LocationEnum::Home,
            LocationEnum::Basement,
            LocationEnum::Mantle,
            LocationEnum::Wardrobe,
        ]);

        if(!$magnifyingGlass)
        {
            throw new PSPInvalidOperationException('Goodness! It\'s so small! You\'ll need a magnifying glass of some kind...');
        }

        $pet = BlueprintHelpers::getPet($em, $user, $request);

        $em->remove($inventory);

        UserUnlockedFeatureHelpers::create($em, $user, UnlockableFeatureEnum::Beehive);

        if($user->getGreenhouse())
            $user->getGreenhouse()->setBeesDismissedOn(new \DateTimeImmutable());

        $beehiveService->createBeehive($user);

        $your = 'your';

        if($magnifyingGlass->getHolder())
            $your = $magnifyingGlass->getHolder()->getName() . '\'s';
        else if($magnifyingGlass->getWearer())
            $your = $magnifyingGlass->getWearer()->getName() . '\'s';

        BlueprintHelpers::rewardHelper(
            $petExperienceService, $responseService, $em,
            $pet,
            PetSkillEnum::Crafts,
            'The blueprint is _super_ tiny, but with the help of ' . $your . ' ' . $magnifyingGlass->getFullItemName() . ', you\'re able to make it all out, and you and ' . $pet->getName() . ' put the thing together! ("Beehive" has been added to the menu!)',
            $pet->getName() . ' put a Beehive together with ' . $user->getName() . '!'
        );

        $em->flush();

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }
}
