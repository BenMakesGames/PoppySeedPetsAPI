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
use App\Functions\ArrayFunctions;
use App\Functions\ItemRepository;
use App\Functions\RecipeRepository;
use App\Model\ItemQuantity;
use App\Service\CookingService;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/item/whisperStone")]
class WhisperStoneController
{
    #[Route("/{inventory}/listen", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function read(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, IRandom $rng,
        UserStatsService $userStatsRepository, CookingService $cookingService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'whisperStone/#/listen');

        $inventory->changeItem(ItemRepository::findOneByName($em, 'Striped Microcline'));

        $complexRecipes = RecipeRepository::findBy(fn($recipe) => mb_substr_count($recipe['ingredients'], ',') >= 2);

        $recipes = $rng->rngNextSubsetFromArray($complexRecipes, 2);

        $ingredients = [];

        foreach($recipes as $recipe)
        {
            $ingredients[] = ArrayFunctions::list_nice(
                array_map(function(ItemQuantity $q) {
                    if($q->quantity === 1)
                        return $q->item->getName();
                    else
                        return $q->quantity . '× ' . $q->item->getName();
                }, InventoryService::deserializeItemList($em, $recipe['ingredients']))
            );
        }

        $message =
            "The stone whispers:\n\n\"To make " . $recipes[0]['name'] . ', combine ' . $ingredients[0] . '. ' .
            'To make ' . $recipes[1]['name'] . ', combine ' . $ingredients[1] . ".\"\n\n"
        ;

        $stat = $userStatsRepository->incrementStat($user, 'Listened to a Whisper Stone');

        if($user->getCookingBuddy())
        {
            $learnedSomethingNew = $cookingService->learnRecipe($user, $recipes[0]['name']);
            $learnedSomethingNew = $cookingService->learnRecipe($user, $recipes[1]['name']) || $learnedSomethingNew;

            if($learnedSomethingNew)
                $message .= "\"I heard that,\" your Cooking Buddy whispers back. (It seems like it learned something new!";
            else
                $message .= "\"I know,\" your Cooking Buddy whispers back. \"I know.\" (I guess it already knew those recipes!";

            if($stat->getValue() === 1)
                $message .= ' ';
        }

        if($stat->getValue() === 1)
            $message .= 'But wait: aren\'t Whisper Stones supposed to reveal dark secrets from the spirit world?';

        if($user->getCookingBuddy())
            $message .= ')';

        $em->flush();

        $responseService->setReloadInventory();

        return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);
    }
}
