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


namespace App\Controller\Item\ChooseAPet;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Enum\PetActivityLogInterestingness;
use App\Enum\PetSkillEnum;
use App\Functions\ArrayFunctions;
use App\Functions\PetActivityLogFactory;
use App\Model\PetChanges;
use App\Service\CommentFormatter;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/item/nightAndDay")]
class NightAndDay
{
    #[Route("/{inventory}", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function useItem(
        Inventory $inventory,
        Request $request,
        ResponseService $responseService,
        EntityManagerInterface $em,
        InventoryService $inventoryService,
        PetExperienceService $petExperienceService,
        CommentFormatter $commentFormatter,
        IRandom $rng,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'nightAndDay');

        $pet = ChooseAPetHelpers::getPet($request, $user, $em);
        $petChanges = new PetChanges($pet);

        $pairOfItems = $rng->rngNextFromArray([
            [ 'Black Baabble', 'White Baabble' ],
            [ 'Black Feathers', 'White Feathers' ],
            [ 'Black Flag', 'White Flag' ],
        ]);

        $subject = $rng->rngNextFromArray([
            'on the duality of night and day; light and dark',
            'on their place in the infinite multiverse',
            'inward'
        ]);

        $messageMiddle = "focused {$subject}, and the {$inventory->getFullItemName()} turned into";
        $itemList = ArrayFunctions::list_nice($pairOfItems);

        $activityLog = PetActivityLogFactory::createReadLog($em, $pet, "%pet:{$pet->getId()}.name% {$messageMiddle} {$itemList}!")
            ->addInterestingness(PetActivityLogInterestingness::PlayerActionResponse)
        ;

        foreach($pairOfItems as $item)
            $inventoryService->petCollectsItem($item, $pet, "{$pet->getName()} {$messageMiddle} this!", $activityLog);

        $petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Arcana ], $activityLog);

        $activityLog->setChanges($petChanges->compare($pet));

        $em->remove($inventory);
        $em->flush();

        return $responseService->itemActionSuccess(
            $commentFormatter->format($activityLog->getEntry()),
            [ 'itemDeleted' => true ]
        );
    }
}