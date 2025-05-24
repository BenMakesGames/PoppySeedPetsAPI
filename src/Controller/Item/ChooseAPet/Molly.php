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

#[Route("/item/molly")]
class Molly
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

        ItemControllerHelpers::validateInventory($user, $inventory, 'molly');

        $pet = ChooseAPetHelpers::getPet($request, $user, $em);
        $petChanges = new PetChanges($pet);
        $skills = $pet->getComputedSkills();

        $quantity = 2 + (int)floor(($skills->getNature()->getTotal() + $skills->getDexterity()->getTotal()) / 3);

        $milkQuantity = $quantity < 4 ? 1 : $rng->rngNextInt(1, (int)floor($quantity / 2));
        $fluffQuantity = max(1, $quantity - $milkQuantity);

        $loot = [
            "{$milkQuantity}× Milk",
            "{$fluffQuantity}× Fluff"
        ];

        $babies = $rng->rngNextInt(3, 5);
        $babyItem = $rng->rngNextBool() ? 'Catmouse Figurine' : 'Tentacat Figurine';

        $howMessyWasIt = $rng->rngNextFromArray([
            'a surprisingly-messy',
            'an astonishingly-shambolic',
            'an unbelievably-chaotic',
        ]);

        $actionDescription = "helped the Molly give birth to a litter of... {$babies} {$babyItem}s?? It was {$howMessyWasIt} affair, during which they collected " . ArrayFunctions::list_nice($loot) . "...";

        $activityLog = PetActivityLogFactory::createReadLog($em, $pet, "%pet:{$pet->getId()}.name% {$actionDescription}")
            ->addInterestingness(PetActivityLogInterestingness::PlayerActionResponse)
        ;

        for($i = 0; $i < $milkQuantity; $i++)
            $inventoryService->petCollectsItem('Creamy Milk', $pet, "{$pet->getName()} collected this while helping a Molly \"give birth\" to some {$babyItem}s...", $activityLog);

        for($i = 0; $i < $fluffQuantity; $i++)
            $inventoryService->petCollectsItem('Fluff', $pet, "{$pet->getName()} collected this while helping a Molly \"give birth\" to some {$babyItem}s...", $activityLog);

        for($i = 0; $i < $babies; $i++)
            $inventoryService->petCollectsItem($babyItem, $pet, "{$pet->getName()} helped a Molly \"give birth\" to this...", $activityLog);

        $petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Nature ], $activityLog);

        $activityLog->setChanges($petChanges->compare($pet));

        $em->remove($inventory);
        $em->flush();

        return $responseService->itemActionSuccess(
            $commentFormatter->format($activityLog->getEntry()),
            [ 'itemDeleted' => true ]
        );
    }
}