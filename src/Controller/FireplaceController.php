<?php
namespace App\Controller;

use App\Entity\Dragon;
use App\Entity\Fireplace;
use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Functions\ArrayFunctions;
use App\Functions\JewishCalendarFunctions;
use App\Repository\DragonRepository;
use App\Repository\InventoryRepository;
use App\Repository\ItemRepository;
use App\Repository\UserQuestRepository;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/fireplace")
 */
class FireplaceController extends PoppySeedPetsController
{
    /**
     * @Route("", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getFireplace(
        InventoryRepository $inventoryRepository, ResponseService $responseService, DragonRepository $dragonRepository
    )
    {
        $user = $this->getUser();

        if(!$user->getUnlockedFireplace() || !$user->getFireplace())
            throw new AccessDeniedHttpException('You haven\'t got a Fireplace, yet!');

        $mantle = $inventoryRepository->findBy([
            'owner' => $user,
            'location' => LocationEnum::MANTLE
        ]);

        $dragon = $dragonRepository->findWhelp($user);

        return $responseService->success(
            [
                'mantle' => $mantle,
                'fireplace' => $user->getFireplace(),
                'whelp' => $dragon
            ],
            [
                SerializationGroupEnum::MY_INVENTORY,
                SerializationGroupEnum::MY_FIREPLACE,
            ]
        );
    }

    /**
     * @Route("/stocking", methods={"PATCH"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function saveStockingSettings(
        Request $request, ResponseService $responseService, EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        if(!$user->getUnlockedFireplace() || !$user->getFireplace())
            throw new AccessDeniedHttpException('You haven\'t got a Fireplace, yet!');

        $appearance = $request->request->getAlnum('appearance');
        $colorA = $request->request->getAlnum('colorA');
        $colorB = $request->request->getAlnum('colorB');

        if(!in_array($appearance, Fireplace::STOCKING_APPEARANCES))
            throw new UnprocessableEntityHttpException('Must choose a stocking appearance...');

        if(!preg_match('/[A-Fa-f0-9]{6}/', $colorA))
            throw new UnprocessableEntityHttpException('Color A is not valid.');

        if(!preg_match('/[A-Fa-f0-9]{6}/', $colorB))
            throw new UnprocessableEntityHttpException('Color B is not valid.');

        $user->getFireplace()
            ->setStockingAppearance($appearance)
            ->setStockingColorA($colorA)
            ->setStockingColorB($colorB)
        ;

        $em->flush();

        return $responseService->success();
    }

    /**
     * @Route("/fuel", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getFireplaceFuel(
        InventoryRepository $inventoryRepository, ResponseService $responseService
    )
    {
        $user = $this->getUser();

        if(!$user->getUnlockedFireplace() || !$user->getFireplace())
            throw new AccessDeniedHttpException('You haven\'t got a Fireplace, yet!');

        $fuel = $inventoryRepository->findFuel($user);

        return $responseService->success($fuel, [ SerializationGroupEnum::FIREPLACE_FUEL ]);
    }

    /**
     * @Route("/whelpFood", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getWhelpFood(
        InventoryRepository $inventoryRepository, ResponseService $responseService, DragonRepository $dragonRepository
    )
    {
        $user = $this->getUser();

        $whelp = $dragonRepository->findWhelp($user);

        if(!$whelp)
            throw new AccessDeniedHttpException('You haven\'t got a Dragon Whelp, yet!');

        $food = $inventoryRepository->createQueryBuilder('i')
            ->andWhere('i.owner=:user')->setParameter('user', $user->getId())
            ->andWhere('i.location=:home')->setParameter('home', LocationEnum::HOME)
            ->join('i.item', 'item')
            ->join('item.food', 'food')
            ->andWhere('(food.spicy > 0 OR food.meaty > 0 OR food.fishy > 0)')
            ->addOrderBy('item.name', 'ASC')
            ->getQuery()
            ->execute()
        ;

        return $responseService->success($food, [ SerializationGroupEnum::MY_INVENTORY ]);
    }

    /**
     * @Route("/feedWhelp", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function feedWhelp(
        Request $request, InventoryRepository $inventoryRepository, ResponseService $responseService,
        InventoryService $inventoryService, EntityManagerInterface $em, DragonRepository $dragonRepository,
        Squirrel3 $squirrel3
    )
    {
        $user = $this->getUser();

        $whelp = $dragonRepository->findWhelp($user);

        if(!$whelp)
            throw new AccessDeniedHttpException('You haven\'t got a Dragon Whelp, yet!');

        if(!$request->request->has('food'))
            throw new UnprocessableEntityHttpException('No items were selected as food???');

        $itemIds = $request->request->get('food');

        if(!is_array($itemIds)) $itemIds = [ $itemIds ];

        /** @var Inventory[] $items */
        $items = $inventoryRepository->findBy([
            'id' => $itemIds,
            'owner' => $user->getId(),
            'location' => LocationEnum::HOME
        ]);

        $items = array_filter($items, function(Inventory $i) {
            return $i->getItem()->getFood() && (
                $i->getItem()->getFood()->getFishy() > 0 || // most foods you feed the whelp are probably fishy
                $i->getItem()->getFood()->getMeaty() > 0 ||
                $i->getItem()->getFood()->getSpicy() > 0
            );
        });

        if(count($items) < count($itemIds))
            throw new UnprocessableEntityHttpException('Some of the food items selected could not be used. That shouldn\'t happen. Reload and try again, maybe?');

        $loot = [];

        foreach($items as $item)
        {
            $em->remove($item);

            $whelp->increaseFood($item->getItem()->getFood()->getFood() + $item->getItem()->getFood()->getSpicy() * 2);

            while($whelp->getFood() >= Dragon::FOOD_REQUIRED_FOR_A_MEAL)
            {
                $whelp->decreaseFood();

                $r = $squirrel3->rngNextInt(1, 100);

                if($r === 1)
                    $loot[] = 'Firestone';          // 1%
                else if($r === 2 || $r === 3)
                    $loot[] = 'Dark Matter';        // 2%
                else if($r <= 8)
                    $loot[] = 'Charcoal';           // 5%
                else if($r <= 28)
                    $loot[] = 'Quintessence';       // 20%
                else
                    $loot[] = 'Liquid-hot Magma';   // 72%
            }
        }

        if(count($loot) > 0)
        {
            sort($loot);

            foreach($loot as $item)
                $inventoryService->receiveItem($item, $user, $user, $whelp->getName() . ' spit this up.', LocationEnum::HOME);

            $responseService->addFlashMessage($whelp->getName() . ' spit up ' . ArrayFunctions::list_nice($loot) . '.');
        }
        else
        {
            $adverb = $squirrel3->rngNextFromArray([
                'happily', 'happily', 'happily', 'excitedly', 'blithely'
            ]);

            $responseService->addFlashMessage($whelp->getName() . ' ' . $adverb . ' devoured your offering.');
        }

        if($whelp->getGrowth() >= 35 * 20)
        {
            $greetingsAndThanks = $squirrel3->rngNextSubsetFromArray(Dragon::GREETINGS_AND_THANKS, 2);

            $whelp
                ->setIsAdult(true)
                ->setGreetings([ $greetingsAndThanks[0]['greeting'], $greetingsAndThanks[1]['greeting'] ])
                ->setThanks([ $greetingsAndThanks[0]['thanks'], $greetingsAndThanks[1]['thanks'] ])
            ;
            $user->setUnlockedDragonDen();
            $responseService->addFlashMessage($whelp->getName() . ' is a whelp no longer! They leave your fireplace and establish a den nearby!');
        }

        $em->flush();

        if($whelp->getIsAdult())
            return $responseService->success();
        else
            return $responseService->success($whelp, [ SerializationGroupEnum::MY_FIREPLACE ]);
    }

    /**
     * @Route("/lookInStocking", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function lookInStocking(
        InventoryService $inventoryService, ResponseService $responseService, EntityManagerInterface $em,
        UserQuestRepository $userQuestRepository, ItemRepository $itemRepository, Squirrel3 $squirrel3
    )
    {
        $user = $this->getUser();
        $now = new \DateTimeImmutable();
        $monthAndDay = $now->format('md');

        if($monthAndDay < 1201)
            throw new AccessDeniedHttpException('It\'s not December!');

        $gotStockingPresent = $userQuestRepository->findOrCreate($user, 'Got a Stocking Present', null);

        if($gotStockingPresent->getValue() === $now->format('Y-m-d'))
            throw new AccessDeniedHttpException('There\'s nothing else in the stocking. Maybe tomorrow?');

        $randomRewards = [
            'Mint', 'Chocolate Bar', 'Charcoal', 'Cheese', 'Crystal Ball', 'Fruit Basket',
            'Glowing Four-sided Die', 'Glowing Six-sided Die', 'Glowing Eight-sided Die', 'Wings',
            'Fluff', 'Paper Bag', 'Plastic Idol', 'Quintessence', 'Secret Seashell', 'Rock'
        ];

        $rewards = [
            null, // 1st
            'Gold Key', // 2nd - International Day for the Abolition of Slavery
            null, // 3rd
            'World\'s Best Sugar Cookie', // 4th - National Cookie Day
            'Mysterious Seed', // 5th - World Soil Day
            'Blue Firework', // 6th - Independence Day (Finland)
            'Candle', // 7th - Day of the Little Candles
            'Fig', // 8th - Bodhi Day
            'Lutefisk', // 9th - Anna's Day
            null, // 10th
            'Liquid-hot Magma', // 11th - International Mountain Day
            'Bungee Cord', // 12th - Jamhuri Day
            null, // 13th
            'Naner', // 14th - Monkey Day
            'Tea Leaves', // 15th - International Tea Day
            'Red Firework', // 16th - Day of Reconciliation
            'Red Umbrella', // 17th - International Day to End Violence Against Sex Workers
            null, // 18th
            'Behatting Scroll', // 19th - no particular holiday; just want to give one of these out
            'String', // 20th - National Ugly Sweater Day (it's stupid, but sure)
            null, // 21st
            'Compass (the Math Kind)', // 22nd - National Mathematics Day
            'Large Radish', // 23rd - Night of the Radishes
            'Fish', // 24th - Feast of the Seven Fishes
            'Santa Hat', // 25th - Christmas
            'Candle', // 26th - 1st day of Kwanzaa (candle-lighting is listed among ceremonies)
            null, // 27th
            'Corn', // 28th - 3rd day of Kwanzaa (corn is listed among symbols)
            null, // 29th
            'Apricot', // 30th - 4th day of Kwanzaa (fresh fruit is listed among symbols)
            'Music Note', // 31st - New Year's Eve/Hogmanay
        ];

        $item = $rewards[$monthAndDay - 1201];

        if(!$item)
        {
            if(JewishCalendarFunctions::isHanukkah($now))
                $item = 'Dreidel';
            else
                $item = $squirrel3->rngNextFromArray($randomRewards);
        }

        $itemObject = $itemRepository->findOneByName($item);

        $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' found this in a stocking over their Fireplace on ' . $now->format('M j, Y') . '.', LocationEnum::HOME, true);

        $messages = [
            'You reach into the stocking and feel around... eventually your fingers find something. You pull it out...',
            'You reach into the stocking, and in one, swift motion extract the gift inside...',
            'You up-end the stocking; something falls out, but you\'re ready...',
            'You squeeze the stocking like a tube of toothpaste, forcing its contents up, and out of the stocking\'s opening...',
            'You peer into the stocking, but all you see darkness. Carefully, you reach inside... and find something! You pull it out as quickly as possible!',
        ];

        $responseService->addFlashMessage(
            $squirrel3->rngNextFromArray($messages) . "\n\n" . ucfirst($itemObject->getNameWithArticle()) . '!'
        );

        $gotStockingPresent->setValue($now->format('Y-m-d'));

        $em->flush();

        return $responseService->success();
    }

    /**
     * @Route("/claimRewards", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function claimRewards(
        InventoryService $inventoryService, ResponseService $responseService, EntityManagerInterface $em,
        UserQuestRepository $userQuestRepository, Squirrel3 $squirrel3
    )
    {
        $user = $this->getUser();

        if(!$user->getUnlockedFireplace() || !$user->getFireplace())
            throw new AccessDeniedHttpException('You haven\'t got a Fireplace, yet!');

        $fireplace = $user->getFireplace();

        if(!$fireplace->getHasReward())
            throw new AccessDeniedHttpException('There\'s nothing unusual in the fireplace right now... (That\'s odd. Reload and try again?)');

        $numItems = min(3, (int)($fireplace->getPoints() / (8 * 60)));

        $rewardLevelBonus = min(7, (int)($fireplace->getCurrentStreak() / (24 * 60)));

        $possibleRewards = [
            'Quintessence',
            'Naner Pancakes',
            'Burnt Log',
            'Burnt Log',
            'Poker',
            'Hot Dog',
            'Glowing Four-sided Die',
            'Glowing Six-sided Die',

            'Fairy Ring', // 1 day
            'Iron Ore', // 2 days
            'Glowing Eight-sided Die', // 3 days
            'Bag of Beans', // 4 days
            'Box of Ores', // 5 days
            'Magic Beans', // 6 days
            'House Fairy', // 7 days
        ];

        $itemsReceived = [];

        for($i = 0; $i < $numItems; $i++)
        {
            $itemName = $possibleRewards[$squirrel3->rngNextInt(0, $squirrel3->rngNextInt(7, 7 + $rewardLevelBonus))];

            if($itemName === 'House Fairy')
            {
                $inventoryService->receiveItem($itemName, $user, $user, $user->getName() . ' found this in their fireplace. (Oh! Hello!)', LocationEnum::HOME);

                // triggers Hyssop letter #3
                $oldValue = $userQuestRepository->findOrCreate($user, 'Can Receive Letters from Fairies', 0);
                if($oldValue->getValue() === 2)
                    $oldValue->setValue(3);
            }
            else if($itemName === 'Burnt Log')
                $inventoryService->receiveItem($itemName, $user, $user, $user->getName() . ' found this in their fireplace. (Nothing surprising there.)', LocationEnum::HOME);
            else if($itemName === 'Poker')
                $inventoryService->receiveItem($itemName, $user, $user, $user->getName() . ' found this in their fireplace. (Oops! How\'d that get left in there!)', LocationEnum::HOME);
            else if($itemName === 'Naner Pancakes' || $itemName === 'Hot Dog')
                $inventoryService->receiveItem($itemName, $user, $user, $user->getName() . ' found this in their fireplace. (Whew! didn\'t burn!)', LocationEnum::HOME);
            else if($squirrel3->rngNextInt(1, 4) === 1)
                $inventoryService->receiveItem($itemName, $user, $user, $user->getName() . ' found this in their fireplace. (Did someone put that in there? It seems like someone put that in there.)', LocationEnum::HOME);
            else
                $inventoryService->receiveItem($itemName, $user, $user, $user->getName() . ' found this in their fireplace. (Is that... normal?)', LocationEnum::HOME);

            $itemsReceived[] = $itemName;
        }

        if($numItems === 3)
            $fireplace->clearPoints();
        else
            $fireplace->spendPoints($numItems * 8 * 60);

        $em->flush();

        if($fireplace->getHeat() >= 2 * 60 && $squirrel3->rngNextInt(1, 3) === 1)
            $responseService->addFlashMessage('You reach inside while the fire is still burning, just like a totally normal person would do, and pull out ' . ArrayFunctions::list_nice($itemsReceived) . '!');
        else
            $responseService->addFlashMessage('You reach inside, and pull out ' . ArrayFunctions::list_nice($itemsReceived) . '!');

        return $responseService->success($fireplace, [ SerializationGroupEnum::MY_FIREPLACE ]);
    }

    /**
     * @Route("/feed", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function feedFireplace(
        Request $request, InventoryRepository $inventoryRepository, ResponseService $responseService,
        EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        if(!$user->getUnlockedFireplace() || !$user->getFireplace())
            throw new AccessDeniedHttpException('You haven\'t got a Fireplace, yet!');

        if(!$request->request->has('fuel'))
            throw new UnprocessableEntityHttpException('No items were selected as fuel???');

        $itemIds = $request->request->get('fuel');

        if(!is_array($itemIds)) $itemIds = [ $itemIds ];

        $items = $inventoryRepository->findFuel($user, $itemIds);

        if(count($items) < count($itemIds))
            throw new UnprocessableEntityHttpException('Some of the fuel items selected could not be used. That shouldn\'t happen. Reload and try again, maybe?');

        $fireplace = $user->getFireplace();

        $fuelNotUsed = [];

        foreach($items as $item)
        {
            // don't feed an item if doing so would waste more than half the item's fuel
            if($fireplace->getHeat() + $item->getItem()->getFuel() / 2 <= Fireplace::MAX_HEAT)
            {
                $fireplace->addHeat($item->getItem()->getFuel());
                $em->remove($item);
            }
            else
            {
                $fuelNotUsed[] = $item->getItem()->getName();
            }
        }

        $em->flush();

        if(count($fuelNotUsed) > 0)
        {
            $responseService->addFlashMessage(
                'The fireplace can only handle so much fire! Adding the ' . ArrayFunctions::list_nice($fuelNotUsed) .
                ' would be wasteful at this point, so ' . (count($fuelNotUsed) == 1 ? 'it was' : 'they were') . ' not used.'
            );
        }

        return $responseService->success($user->getFireplace(), [ SerializationGroupEnum::MY_FIREPLACE ]);
    }

    /**
     * @Route("/mantle/{user}", methods={"GET"}, requirements={"user"="\d+"})
     */
    public function getMantle(User $user, InventoryRepository $inventoryRepository, ResponseService $responseService)
    {
        $inventory = $inventoryRepository->findBy([
            'owner' => $user,
            'location' => LocationEnum::MANTLE
        ]);

        return $responseService->success($inventory, [ SerializationGroupEnum::FIREPLACE_MANTLE ]);
    }
}
