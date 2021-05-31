<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Entity\Recipe;
use App\Functions\ArrayFunctions;
use App\Model\ItemQuantity;
use App\Repository\ItemRepository;
use App\Repository\RecipeRepository;
use App\Repository\UserStatsRepository;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/whisperStone")
 */
class WhisperStoneController extends PoppySeedPetsItemController
{
    /**
     * @Route("/{inventory}/listen", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function read(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Squirrel3 $squirrel3,
        ItemRepository $itemRepository, RecipeRepository $recipeRepository, InventoryService $inventoryService,
        UserStatsRepository $userStatsRepository
    )
    {
        $this->validateInventory($inventory, 'whisperStone/#/listen');

        $user = $this->getUser();

        $inventory->changeItem($itemRepository->findOneByName('Striped Microcline'));

        $recipeCount = (int)$recipeRepository->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->andWhere('r.ingredients LIKE :twoCommas')
            ->setParameter('twoCommas', '%,%,%')
            ->getQuery()
            ->getSingleScalarResult()
        ;

        $recipeToGet = $squirrel3->rngNextInt(0, $recipeCount - 1);

        /** @var Recipe[] $recipes */
        $recipes = [];

        $recipes[] = $recipeRepository->createQueryBuilder('r')
            ->andWhere('r.ingredients LIKE :twoCommas')
            ->setParameter('twoCommas', '%,%,%')
            ->setFirstResult($recipeToGet)
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult()
        ;

        $recipeToGet = $squirrel3->rngNextInt(0, $recipeCount - 2);

        $recipes[] = $recipeRepository->createQueryBuilder('r')
            ->andWhere('r.ingredients LIKE :twoCommas')
            ->setParameter('twoCommas', '%,%,%')
            ->andWhere('r.id != :recipe1Id')
            ->setParameter('recipe1Id', $recipes[0]->getId())
            ->setFirstResult($recipeToGet)
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult()
        ;

        $ingredients = [];

        foreach($recipes as $recipe)
        {
            $ingredients[] = ArrayFunctions::list_nice(
                array_map(function(ItemQuantity $q) {
                    if($q->quantity === 1)
                        return $q->item->getName();
                    else
                        return $q->quantity . '× ' . $q->item->getName();
                }, $inventoryService->deserializeItemList($recipe->getIngredients()))
            );
        }

        $message =
            "The stone whispers:\n\n\"To make " . $recipes[0]->getName() . ', combine ' . $ingredients[0] . '. ' .
            'To make ' . $recipes[1]->getName() . ', combine ' . $ingredients[1] . ".\"\n\n"
        ;

        $stat = $userStatsRepository->incrementStat($user, 'Listened to a Whisper Stone');

        if($stat->getValue() === 1)
            $message .= 'Wait, aren\'t Whisper Stones supposed to reveal dark secrets from the spirit world?';
        else
        {
            $message .= $squirrel3->rngNextFromArray([
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
