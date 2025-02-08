<?php
declare(strict_types=1);

namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Entity\KnownRecipes;
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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/whisperStone")]
class WhisperStoneController extends AbstractController
{
    #[Route("/{inventory}/listen", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function read(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, IRandom $rng,
        UserStatsService $userStatsRepository, CookingService $cookingService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

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
