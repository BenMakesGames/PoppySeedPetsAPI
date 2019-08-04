<?php
namespace App\Controller;

use App\Entity\Inventory;
use App\Entity\KnownRecipes;
use App\Enum\SerializationGroupEnum;
use App\Enum\UserStatEnum;
use App\Repository\InventoryRepository;
use App\Repository\ItemRepository;
use App\Repository\KnownRecipesRepository;
use App\Repository\RecipeRepository;
use App\Repository\UserStatsRepository;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/inventory")
 */
class InventoryController extends PsyPetsController
{
    /**
     * @Route("/my", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getMyInventory(ResponseService $responseService, InventoryRepository $inventoryRepository)
    {
        $inventory = $inventoryRepository->findBy([ 'owner' => $this->getUser() ], [ 'modifiedOn' => 'DESC' ]);
        return $responseService->success($inventory, SerializationGroupEnum::MY_INVENTORY);
    }

    /**
     * @Route("/my/quantities", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function availableRecipes(
        ResponseService $responseService, ItemRepository $itemRepository
    )
    {
        $user = $this->getUser();

        $inventory = $itemRepository->getInventoryQuantities($user);

        return $responseService->success($inventory, SerializationGroupEnum::MY_INVENTORY);
    }

    /**
     * @Route("/prepare", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function prepareRecipe(
        Request $request, ResponseService $responseService, InventoryRepository $inventoryRepository,
        RecipeRepository $recipeRepository, InventoryService $inventoryService, EntityManagerInterface $em,
        UserStatsRepository $userStatsRepository, KnownRecipesRepository $knownRecipesRepository
    )
    {
        $user = $this->getUser();

        $inventoryIds = $request->request->get('inventory');
        if(!\is_array($inventoryIds)) $inventoryIds = [ $inventoryIds ];

        $inventory = $inventoryRepository->findBy([
            'owner' => $user,
            'id' => $inventoryIds
        ]);

        if(\count($inventory) !== \count($inventoryIds))
            throw new UnprocessableEntityHttpException('Some of the items could not be found??');

        $quantities = $inventoryService->buildQuantitiesFromInventory($inventory);
        $recipe = $recipeRepository->findOneBy([ 'ingredients' => $inventoryService->serializeItemList($quantities) ]);

        if(!$recipe)
            throw new UnprocessableEntityHttpException('You can\'t make anything with those ingredients.');

        foreach($inventory as $i)
            $em->remove($i);

        $makes = $inventoryService->deserializeItemList($recipe->getMakes());

        $newInventory = $inventoryService->giveInventory($makes, $user, $user, $user->getName() . ' prepared this.');

        $userStatsRepository->incrementStat($user, UserStatEnum::COOKED_SOMETHING);

        if($inventoryService->countInventory($user, 'Cooking Buddy') > 0)
        {
            $alreadyKnownRecipe = $knownRecipesRepository->findOneBy([
                'user' => $user,
                'recipe' => $recipe
            ]);

            if(!$alreadyKnownRecipe)
            {
                $knownRecipe = (new KnownRecipes())
                    ->setUser($user)
                    ->setRecipe($recipe)
                ;

                $em->persist($knownRecipe);

                $userStatsRepository->incrementStat($user, UserStatEnum::RECIPES_LEARNED_BY_COOKING_BUDDY);
            }
        }

        $em->flush();

        return $responseService->success($newInventory, SerializationGroupEnum::MY_INVENTORY);
    }

    /**
     * @Route("/{inventory}/sellPrice", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function setSellPrice(
        Inventory $inventory, ResponseService $responseService, Request $request, EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        if($inventory->getOwner()->getId() !== $user->getId())
            throw new NotFoundHttpException();

        $price = $request->request->getInt('price', 0);

        if($price >= $user->getMaxSellPrice())
            throw new UnprocessableEntityHttpException('You cannot list items for more than ' . $user->getMaxSellPrice() . ' moneys. See the Market Manager to see if you can increase this limit!');

        if($price <= 0)
            $inventory->setSellPrice(null);
        else
            $inventory->setSellPrice($price);

        $em->flush();

        return $responseService->success($inventory->getSellPrice());
    }

    /**
     * @Route("/throwAway", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function throwAway(
        Request $request, ResponseService $responseService, InventoryRepository $inventoryRepository,
        EntityManagerInterface $em, UserStatsRepository $userStatsRepository
    )
    {
        $user = $this->getUser();

        $inventoryIds = $request->request->get('inventory');
        if(!\is_array($inventoryIds)) $inventoryIds = [ $inventoryIds ];

        $inventory = $inventoryRepository->findBy([
            'owner' => $user,
            'id' => $inventoryIds
        ]);

        if(\count($inventory) !== \count($inventoryIds))
            throw new UnprocessableEntityHttpException('Some of the items could not be found??');

        foreach($inventory as $i)
        {
            if($i->getItem()->hasUseAction('bug/#/putOutside'))
                $userStatsRepository->incrementStat($user, UserStatEnum::BUGS_PUT_OUTSIDE);

            $em->remove($i);
        }

        $userStatsRepository->incrementStat($user, UserStatEnum::ITEMS_THROWN_AWAY, count($inventory));

        $em->flush();

        return $responseService->success();
    }
}