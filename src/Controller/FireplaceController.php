<?php
namespace App\Controller;

use App\Entity\Fireplace;
use App\Entity\PetActivityLog;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Functions\ArrayFunctions;
use App\Functions\GrammarFunctions;
use App\Functions\StringFunctions;
use App\Repository\InventoryRepository;
use App\Service\InventoryService;
use App\Service\ResponseService;
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
        InventoryRepository $inventoryRepository, ResponseService $responseService
    )
    {
        $user = $this->getUser();

        if(!$user->getUnlockedFireplace() || !$user->getFireplace())
            throw new AccessDeniedHttpException('You haven\'t got a Fireplace, yet!');

        $mantle = $inventoryRepository->findBy([
            'owner' => $user,
            'location' => LocationEnum::MANTLE
        ]);

        return $responseService->success(
            [
                'mantle' => $mantle,
                'fireplace' => $user->getFireplace(),
            ],
            [
                SerializationGroupEnum::MY_INVENTORY,
                SerializationGroupEnum::MY_FIREPLACE,
            ]
        );
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

        return $responseService->success($fuel, SerializationGroupEnum::FIREPLACE_FUEL);
    }

    /**
     * @Route("/claimRewards", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function claimRewards(
        InventoryService $inventoryService, ResponseService $responseService, EntityManagerInterface $em
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
            $itemName = $possibleRewards[mt_rand(0, mt_rand(7, 7 + $rewardLevelBonus))];

            if($itemName === 'Burnt Log')
                $inventoryService->receiveItem($itemName, $user, $user, $user->getName() . ' found this in their fireplace. (Nothing surprising there.)', LocationEnum::HOME);
            else if($itemName === 'Poker')
                $inventoryService->receiveItem($itemName, $user, $user, $user->getName() . ' found this in their fireplace. (Oops! How\'d that get left in there!)', LocationEnum::HOME);
            else if($itemName === 'Naner Pancakes' || $itemName === 'Hot Dog')
                $inventoryService->receiveItem($itemName, $user, $user, $user->getName() . ' found this in their fireplace. (Whew! didn\'t burn!)', LocationEnum::HOME);
            else if(mt_rand(1, 4) === 1)
                $inventoryService->receiveItem($itemName, $user, $user, $user->getName() . ' found this in their fireplace. (Did someone put that it there? It seems like someone put that it there.)', LocationEnum::HOME);
            else
                $inventoryService->receiveItem($itemName, $user, $user, $user->getName() . ' found this in their fireplace. (Is that... normal?)', LocationEnum::HOME);

            $itemsReceived[] = $itemName;
        }

        if($numItems === 3)
            $fireplace->clearPoints();
        else
            $fireplace->spendPoints($numItems * 8 * 60);

        $em->flush();

        if($fireplace->getHeat() >= 2 * 60 && mt_rand(1, 3) === 1)
            $responseService->addActivityLog((new PetActivityLog())->setEntry('You reach inside while the fire is still burning, just like a totally normal person would do, and pull out ' . ArrayFunctions::list_nice($itemsReceived) . '!'));
        else
            $responseService->addActivityLog((new PetActivityLog())->setEntry('You reach inside, and pull out ' . ArrayFunctions::list_nice($itemsReceived) . '!'));

        return $responseService->success($fireplace, SerializationGroupEnum::MY_FIREPLACE);
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

        $items = $inventoryRepository->findBy([
            'id' => $itemIds,
            'owner' => $user->getId(),
            'location' => LocationEnum::HOME
        ]);

        if(count($items) < count($itemIds))
            throw new UnprocessableEntityHttpException('Some of the fuel items selected could not be used. That shouldn\'t happen. Reload and try again, maybe?');

        $fireplace = $user->getFireplace();

        foreach($items as $item)
        {
            // don't feed an item if doing so would waste more than half the item's fuel
            if($fireplace->getHeat() + $item->getItem()->getFuel() / 2 <= Fireplace::MAX_HEAT)
            {
                $fireplace->addHeat($item->getItem()->getFuel());
                $em->remove($item);
            }
        }

        $em->flush();

        return $responseService->success($user->getFireplace(), SerializationGroupEnum::MY_FIREPLACE);
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

        return $responseService->success($inventory, SerializationGroupEnum::FIREPLACE_MANTLE);
    }
}