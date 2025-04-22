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
use App\Entity\User;
use App\Enum\PetSkillEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Repository\InventoryRepository;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item")]
class BirdBathBlueprintController extends AbstractController
{
    #[Route("/birdBathBlueprint/{inventory}", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function buildBirdBath(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        PetExperienceService $petExperienceService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'birdBathBlueprint');

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Greenhouse))
            return $responseService->error(400, [ 'You need a Greenhouse to build a Bird Bath!' ]);

        if($user->getGreenhouse()->getHasBirdBath())
            return $responseService->error(200, [ 'Your Greenhouse already has a Bird Bath!' ]);

        $pet = BlueprintHelpers::getPet($em, $user, $request);

        $ironBar = InventoryRepository::findOneToConsume($em, $user, 'Iron Bar');

        if(!$ironBar)
            return $responseService->error(422, [ 'Hm... you\'re going to need an Iron Bar to make this...' ]);

        $em->remove($ironBar);
        $em->remove($inventory);

        $user->getGreenhouse()->setHasBirdBath(true);

        $flashMessage = 'You build a Bird Bath with ' . $pet->getName() . '!';

        if(strtolower($pet->getName()[0]) === 'b')
            $flashMessage .= ' (How alliterative!)';

        BlueprintHelpers::rewardHelper(
            $petExperienceService, $responseService, $em,
            $pet,
            PetSkillEnum::CRAFTS,
            $flashMessage,
            $pet->getName() . ' built a Bird Bath in the Greenhouse with ' . $user->getName() . '!'
        );

        $em->flush();

        return $responseService->itemActionSuccess(
            null,
            [ 'itemDeleted' => true ]
        );
    }
}
