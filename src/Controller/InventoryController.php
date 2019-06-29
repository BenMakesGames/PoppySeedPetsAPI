<?php
namespace App\Controller;

use App\Enum\SerializationGroup;
use App\Repository\InventoryRepository;
use App\Repository\ItemRepository;
use App\Repository\RecipeRepository;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
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
        return $responseService->success($inventory, SerializationGroup::MY_INVENTORY);
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

        return $responseService->success($inventory, SerializationGroup::MY_INVENTORY);
    }

    /**
     * @Route("/prepare", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function prepareRecipe(
        Request $request, ResponseService $responseService, InventoryRepository $inventoryRepository,
        RecipeRepository $recipeRepository, InventoryService $inventoryService, EntityManagerInterface $em
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

        $newInventory = $inventoryService->giveInventory($makes, $user, $user);

        $em->flush();

        return $responseService->success($newInventory, SerializationGroup::MY_INVENTORY);
    }
}