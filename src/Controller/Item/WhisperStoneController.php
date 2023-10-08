<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Entity\User;
use App\Functions\ArrayFunctions;
use App\Functions\ItemRepository;
use App\Functions\RecipeRepository;
use App\Model\ItemQuantity;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/whisperStone")
 */
class WhisperStoneController extends AbstractController
{
    /**
     * @Route("/{inventory}/listen", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function read(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, IRandom $rng,
        InventoryService $inventoryService, UserStatsService $userStatsRepository
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
                }, $inventoryService->deserializeItemList($recipe['ingredients']))
            );
        }

        $message =
            "The stone whispers:\n\n\"To make " . $recipes[0]['name'] . ', combine ' . $ingredients[0] . '. ' .
            'To make ' . $recipes[1]['name'] . ', combine ' . $ingredients[1] . ".\"\n\n"
        ;

        $stat = $userStatsRepository->incrementStat($user, 'Listened to a Whisper Stone');

        if($stat->getValue() === 1)
            $message .= 'Wait, aren\'t Whisper Stones supposed to reveal dark secrets from the spirit world?';
        else
        {
            $message .= $rng->rngNextFromArray([
                'Thanks, rock!',
                'This Whisper Stone seems to have knowledge within a very specific domain.',
                'Might be worth trying sometime?',
                'Two recipes with one stone!',
                'Whisper Stones are often said to sound creepy, but this one seemed nice enough.',
                'Then its blue glow subsides, leaving you with an ordinary chunk of Striped Microcline.',
                'Whose voice is that, anyway? Is it the rock\'s?',
            ]);
        }

        $em->flush();

        $responseService->setReloadInventory();

        return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);
    }
}
