<?php
namespace App\Controller;

use App\Entity\Inventory;
use App\Entity\KnownRecipes;
use App\Enum\LocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Enum\UserStatEnum;
use App\Functions\ArrayFunctions;
use App\Repository\InventoryRepository;
use App\Repository\ItemRepository;
use App\Repository\KnownRecipesRepository;
use App\Repository\RecipeRepository;
use App\Repository\UserRepository;
use App\Repository\UserStatsRepository;
use App\Service\Filter\InventoryFilterService;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/inventory")
 */
class InventoryController extends PoppySeedPetsController
{
    /**
     * @Route("/my", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getMyHouseInventory(
        ResponseService $responseService, InventoryRepository $inventoryRepository
    )
    {
        $inventory = $inventoryRepository->findBy(
            [
                'owner' => $this->getUser(),
                'location' => LocationEnum::HOME
            ],
            [ 'modifiedOn' => 'DESC' ]
        );
        return $responseService->success($inventory, SerializationGroupEnum::MY_INVENTORY);
    }

    /**
     * @Route("/my/{location}", methods={"GET"}, requirements={"location"="\d+"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getMyInventory(
        Request $request, ResponseService $responseService, InventoryFilterService $inventoryFilterService,
        int $location
    )
    {
        if(!LocationEnum::isAValue($location))
            throw new UnprocessableEntityHttpException('Invalid location given.');

        $user = $this->getUser();

        $inventoryFilterService->addRequiredFilter('user', $user->getId());
        $inventoryFilterService->addRequiredFilter('location', $location);

        $inventory = $inventoryFilterService->getResults($request->query);

        return $responseService->success($inventory, [ SerializationGroupEnum::FILTER_RESULTS, SerializationGroupEnum::MY_INVENTORY ]);
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

        $locationOfFirstItem = $inventory[0]->getLocation();

        if(count($inventory) > 1)
        {
            if(ArrayFunctions::any($inventory, function(Inventory $i) use($locationOfFirstItem) {
                return $i->getLocation() !== $locationOfFirstItem;
            }))
            {
                throw new UnprocessableEntityHttpException('All of the items must be in the same location.');
            }
        }

        $quantities = $inventoryService->buildQuantitiesFromInventory($inventory);
        $recipe = $recipeRepository->findOneBy([ 'ingredients' => $inventoryService->serializeItemList($quantities) ]);

        if(!$recipe)
            throw new UnprocessableEntityHttpException('You can\'t make anything with those ingredients.');

        foreach($inventory as $i)
            $em->remove($i);

        $makes = $inventoryService->deserializeItemList($recipe->getMakes());

        $newInventory = $inventoryService->giveInventory($makes, $user, $user, $user->getName() . ' prepared this.', $locationOfFirstItem);

        $userStatsRepository->incrementStat($user, UserStatEnum::COOKED_SOMETHING);

        if($inventoryService->countInventory($user, 'Cooking Buddy', $locationOfFirstItem) > 0)
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
        EntityManagerInterface $em, UserStatsRepository $userStatsRepository, UserRepository $userRepository
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

        $givingTree = $userRepository->findOneByEmail('giving-tree@poppyseedpets.com');

        if(!$givingTree)
            throw new HttpException(500, 'The "Giving Tree" NPC does not exist in the database!');

        foreach($inventory as $i)
        {
            if($i->getItem()->hasUseAction('bug/#/putOutside'))
            {
                $userStatsRepository->incrementStat($user, UserStatEnum::BUGS_PUT_OUTSIDE);
                $em->remove($i);
            }
            else if(mt_rand(1, 10) === 1)
            {
                $i
                    ->setOwner($givingTree)
                    ->setLocation(LocationEnum::HOME)
                    ->setSellPrice(null)
                    ->addComment($user->getName() . ' threw this item away, but it found its way to The Giving Tree.')
                ;

                if($i->getHolder())
                    $i->getHolder()->setTool(null);

                if($i->getWearer())
                    $i->getWearer()->setHat(null);
            }
            else
                $em->remove($i);
        }

        $userStatsRepository->incrementStat($user, UserStatEnum::ITEMS_THROWN_AWAY, count($inventory));

        $em->flush();

        return $responseService->success();
    }

    /**
     * @Route("/moveTo/{location}", methods={"POST"}, requirements={"location"="\d+"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function moveInventory(
        int $location, Request $request, ResponseService $responseService, InventoryRepository $inventoryRepository,
        EntityManagerInterface $em
    )
    {
        if(!LocationEnum::isAValue($location))
            throw new UnprocessableEntityHttpException('Invalid location given.');

        $user = $this->getUser();

        $inventoryIds = $request->request->get('inventory');
        if(!\is_array($inventoryIds)) $inventoryIds = [ $inventoryIds ];

        $inventory = $inventoryRepository->findBy([
            'owner' => $user,
            'id' => $inventoryIds
        ]);

        if(\count($inventory) !== \count($inventoryIds))
            throw new UnprocessableEntityHttpException('Some of the items could not be found??');

        $itemsInHouse = (int)$inventoryRepository->countItemsInHouse($user);

        if($location === LocationEnum::HOME && $itemsInHouse + count($inventory) > $user->getMaxInventory())
            throw new UnprocessableEntityHttpException('You do not have enough space in your house!');

        $unequippedAPet = false;

        foreach($inventory as $i)
        {
            $i
                ->setLocation($location)
                ->setModifiedOn()
            ;

            if($location !== LocationEnum::HOME && $i->getHolder())
            {
                $i->getHolder()->setTool(null);
                $unequippedAPet = true;
            }
        }

        $em->flush();

        $data = [];

        if($unequippedAPet)
            $data['reloadPets'] = true;

        return $responseService->success($data);
    }
}