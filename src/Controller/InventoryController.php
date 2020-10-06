<?php
namespace App\Controller;

use App\Entity\Inventory;
use App\Entity\KnownRecipes;
use App\Enum\LocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Enum\UserStatEnum;
use App\Functions\ArrayFunctions;
use App\Functions\GrammarFunctions;
use App\Repository\InventoryRepository;
use App\Repository\ItemRepository;
use App\Repository\KnownRecipesRepository;
use App\Repository\RecipeRepository;
use App\Repository\UserRepository;
use App\Repository\UserStatsRepository;
use App\Service\CalendarService;
use App\Service\CookingService;
use App\Service\ToolBonusService;
use App\Service\Filter\InventoryFilterService;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
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

        $inventoryFilterService->setUser($user);

        $inventory = $inventoryFilterService->getResults($request->query);

        return $responseService->success($inventory, [ SerializationGroupEnum::FILTER_RESULTS, SerializationGroupEnum::MY_INVENTORY ]);
    }

    /**
     * @Route("/prepare", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function prepareRecipe(
        Request $request, ResponseService $responseService, InventoryRepository $inventoryRepository,
        InventoryService $inventoryService, EntityManagerInterface $em, CookingService $cookingService,
        ToolBonusService $enchantmentService
    )
    {
        $user = $this->getUser();

        $inventoryIds = $request->request->get('inventory');
        if(!\is_array($inventoryIds)) $inventoryIds = [ $inventoryIds ];

        $inventory = $inventoryRepository->findBy([
            'owner' => $user,
            'id' => $inventoryIds
        ]);

        if(count($inventory) !== count($inventoryIds))
            throw new UnprocessableEntityHttpException('Some of the items could not be found??');

        if(count($inventory) === 0)
            throw new UnprocessableEntityHttpException('You gotta\' select at least ONE item!');

        if(!$inventoryService->inventoryInSameLocation($inventory))
            throw new UnprocessableEntityHttpException('All of the items must be in the same location.');

        if(count($inventory) === 2)
        {
            $tool = ArrayFunctions::find_one($inventory, function(Inventory $i) use($inventoryIds) { return $i->getId() === $inventoryIds[0]; });
            $bonus = ArrayFunctions::find_one($inventory, function(Inventory $i) use($inventoryIds) { return $i->getId() === $inventoryIds[1]; });

            try
            {
                $enchanted = null;

                if($tool->getItem()->getTool() && $bonus->getItem()->getEnchants())
                {
                    $enchantmentService->enchant($tool, $bonus);
                    $enchanted = $tool;
                }
                else if($bonus->getItem()->getTool() && $tool->getItem()->getEnchants())
                {
                    $enchantmentService->enchant($bonus, $tool);
                    $enchanted = $bonus;
                }

                if($enchanted)
                {
                    $newName = $enchantmentService->getNameWithBonus($enchanted);

                    $responseService->addFlashMessageString('The ' . $enchanted->getItem()->getName() . ' is now ' . GrammarFunctions::indefiniteArticle($newName) . ' ' . $newName . '!');

                    $em->flush();

                    return $responseService->success($enchanted, SerializationGroupEnum::MY_INVENTORY);
                }
            }
            catch(\InvalidArgumentException $exception)
            {
                throw new UnprocessableEntityHttpException($exception->getMessage(), $exception);
            }
        }

        $newInventory = $cookingService->prepareRecipe($user, $inventory);

        // do this before checking if anything was made
        // because if NOTHING was made, a record in "RecipeAttempted" was made :P
        $em->flush();

        if($newInventory === null)
            throw new UnprocessableEntityHttpException('You can\'t make anything with those ingredients.');

        return $responseService->success($newInventory, SerializationGroupEnum::MY_INVENTORY);
    }

    /**
     * @Route("/{inventory}/removeBonus", methods={"PATCH"}, requirements={"inventory"="\d+"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function removeBonus(Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em)
    {
        $user = $this->getUser();

        if($inventory->getOwner()->getId() !== $user->getId())
            throw new NotFoundHttpException('That item does not belong to you.');

        $inventory->setEnchantment(null);

        $em->flush();

        return $responseService->success();
    }

    /**
     * @Route("/{inventory}/sellPrice", methods={"POST"}, requirements={"inventory"="\d+"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function setSellPrice(
        Inventory $inventory, ResponseService $responseService, Request $request, EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        if($user->getUnlockedMarket() === null)
            throw new AccessDeniedHttpException('You have not yet unlocked this feature.');

        if($inventory->getOwner()->getId() !== $user->getId())
            throw new NotFoundHttpException('That item does not belong to you.');

        if($inventory->getLockedToOwner())
            throw new UnprocessableEntityHttpException('This item is locked to your account. It cannot be sold, traded, etc.');

        $price = $request->request->getInt('price', 0);

        if($price > $user->getMaxSellPrice())
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
        EntityManagerInterface $em, UserStatsRepository $userStatsRepository, UserRepository $userRepository,
        CalendarService $calendarService
    )
    {
        $user = $this->getUser();

        $inventoryIds = $request->request->get('inventory');
        if(!\is_array($inventoryIds)) $inventoryIds = [ $inventoryIds ];

        $inventory = $inventoryRepository->findBy([
            'owner' => $user,
            'id' => $inventoryIds
        ]);

        if(count($inventory) !== count($inventoryIds))
            throw new UnprocessableEntityHttpException('Some of the items could not be found??');

        $givingTree = $userRepository->findOneByEmail('giving-tree@poppyseedpets.com');

        if(!$givingTree)
            throw new HttpException(500, 'The "Giving Tree" NPC does not exist in the database!');

        $givingTreeHoliday = $calendarService->isValentines() || $calendarService->isPiDayOrWhiteDay();

        $totalRecycleValue = 0;

        foreach($inventory as $i)
        {
            if($i->getItem()->hasUseAction('bug/#/putOutside'))
            {
                $userStatsRepository->incrementStat($user, UserStatEnum::BUGS_PUT_OUTSIDE);
                $em->remove($i);
            }
            else if((mt_rand(1, 10) === 1 || $givingTreeHoliday) && !$i->getLockedToOwner())
            {
                $i
                    ->setOwner($givingTree)
                    ->setLocation(LocationEnum::HOME)
                    ->setSellPrice(null)
                    ->addComment($user->getName() . ' recycled this item, and it found its way to The Giving Tree!')
                ;

                if($i->getHolder()) $i->getHolder()->setTool(null);
                if($i->getWearer()) $i->getWearer()->setHat(null);
            }
            else
                $em->remove($i);

            $totalRecycleValue += $i->getItem()->getRecycleValue();
        }

        $userStatsRepository->incrementStat($user, UserStatEnum::ITEMS_RECYCLED, count($inventory));

        if($totalRecycleValue > 0)
        {
            $user->increaseRecyclePoints($totalRecycleValue);

            if($user->getUnlockedRecycling() === null)
                $user->setUnlockedRecycling();
        }

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

        $allowedLocations = [ LocationEnum::HOME ];

        if($user->getUnlockedFireplace())
            $allowedLocations[] = LocationEnum::MANTLE;

        if($user->getUnlockedBasement())
            $allowedLocations[] = LocationEnum::BASEMENT;

        if(!in_array($location, $allowedLocations))
            throw new UnprocessableEntityHttpException('Invalid location given.');

        $inventoryIds = $request->request->get('inventory');
        if(!\is_array($inventoryIds)) $inventoryIds = [ $inventoryIds ];

        if(count($inventoryIds) >= 200)
            throw new UnprocessableEntityHttpException('Oh, goodness, please don\'t try to move more than 200 items at a time. Sorry.');

        $inventory = $inventoryRepository->createQueryBuilder('i')
            ->andWhere('i.owner=:user')
            ->andWhere('i.id IN (:inventoryIds)')
            ->andWhere('i.location IN (:allowedLocations)')
            ->setParameter('user', $user->getId())
            ->setParameter('inventoryIds', $inventoryIds)
            ->setParameter('allowedLocations', $allowedLocations)
            ->getQuery()
            ->execute()
        ;

        if(count($inventory) !== count($inventoryIds))
            throw new UnprocessableEntityHttpException('Some of the items could not be found??');

        $itemsInTargetLocation = (int)$inventoryRepository->countItemsInLocation($user, $location);

        if($location === LocationEnum::HOME)
        {
            if ($itemsInTargetLocation + count($inventory) > $user->getMaxInventory())
                throw new UnprocessableEntityHttpException('You do not have enough space in your house!');
        }

        if($location === LocationEnum::BASEMENT)
        {
            if ($itemsInTargetLocation + count($inventory) > 10000)
                throw new UnprocessableEntityHttpException('You do not have enough space in the basement!');
        }

        if($location === LocationEnum::MANTLE)
        {
            if ($itemsInTargetLocation + count($inventory) > $user->getFireplace()->getMantleSize())
                throw new UnprocessableEntityHttpException('The mantle only has space for ' . $user->getFireplace()->getMantleSize() . ' items.');
        }

        foreach($inventory as $i)
        {
            $i
                ->setLocation($location)
                ->setModifiedOn()
            ;
        }

        $em->flush();

        return $responseService->success();
    }
}
