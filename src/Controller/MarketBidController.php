<?php
namespace App\Controller;

use App\Entity\MarketBid;
use App\Enum\EnumInvalidValueException;
use App\Enum\LocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Repository\InventoryRepository;
use App\Repository\ItemRepository;
use App\Repository\MarketBidRepository;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\TransactionService;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Location;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/marketBid")
 */
class MarketBidController extends PoppySeedPetsController
{
    /**
     * @Route("", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getMyBids(ResponseService $responseService, MarketBidRepository $marketBidRepository)
    {
        $user = $this->getUser();

        if(!$user->getUnlockedMarket())
            throw new AccessDeniedHttpException('You haven\'t unlocked this feature, yet!');

        $myBids = $marketBidRepository->findBy([ 'user' => $user ], [ 'createdOn' => 'DESC' ]);

        return $responseService->success($myBids, [ SerializationGroupEnum::MY_MARKET_BIDS ]);
    }

    /**
     * @Route("", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function createBid(
        Request $request, ResponseService $responseService, ItemRepository $itemRepository,
        TransactionService $transactionService, MarketBidRepository $marketBidRepository,
        InventoryService $inventoryService, EntityManagerInterface $em, InventoryRepository $inventoryRepository
    )
    {
        $user = $this->getUser();

        if(!$user->getUnlockedMarket())
            throw new AccessDeniedHttpException('You haven\'t unlocked this feature, yet!');

        $itemsAtHome = $inventoryService->countTotalInventory($user, LocationEnum::HOME);

        if(!$user->getUnlockedBasement())
            $location = LocationEnum::HOME;
        else
        {
            $location = $request->request->getInt('location', LocationEnum::HOME);

            if(!LocationEnum::isAValue($location))
                throw new UnprocessableEntityHttpException('You must select a location for the item to go to.');
        }

        if($itemsAtHome >= 100)
        {
            if(!$user->getUnlockedBasement())
                throw new UnprocessableEntityHttpException('Your house is already overflowing with items! You\'ll need to clear some out before you can create any new bids.');

            $itemsInBasement = $inventoryService->countTotalInventory($user, LocationEnum::BASEMENT);

            if($itemsInBasement >= 10000)
                throw new UnprocessableEntityHttpException('Your house and basement are already overflowing with items! You\'ll need to clear some space before you can create any new bids.');
        }

        $itemId = $request->request->getInt('item');

        $item = $itemRepository->find($itemId);

        if(!$item)
            throw new NotFoundHttpException('Could not find that item.');

        $quantity = $request->request->getInt('quantity');

        if($quantity < 1)
            throw new UnprocessableEntityHttpException('You can\'t bid on ' . $quantity . ' number of items.');

        $currentQuantity = $marketBidRepository->getTotalQuantity($user);

        if($currentQuantity + $quantity > $user->getMaxMarketBids())
            throw new UnprocessableEntityHttpException('You can only have bids out on ' . $user->getMaxMarketBids() . ' items at a time.');

        $bid = $request->request->getInt('bid');

        $availableToBuy = (int)$inventoryRepository->createQueryBuilder('i')
            ->select('COUNT(i.id)')
            ->andWhere('i.owner!=:user')
            ->andWhere('i.sellPrice<=:price')
            ->andWhere('i.item=:item')
            ->setParameter('user', $user->getId())
            ->setParameter('price', $bid / 1.02)
            ->setParameter('item', $itemId)
            ->getQuery()
            ->getSingleScalarResult()
        ;

        if($availableToBuy > 0)
            throw new UnprocessableEntityHttpException('Someone is currently selling ' . $item->getName() . ' for less than or equal to that price! [Go buy those up, first!](/market?filter.name=' . urlencode($item->getName()) . ')');

        if($bid < 2)
            throw new UnprocessableEntityHttpException('No one can sell an item for less than 2~~m~~, so bidding for less than that wouldn\'t ever work :P');

        if($bid * $quantity > $user->getMoneys())
            throw new UnprocessableEntityHttpException('That would cost a total of ' . ($bid * $quantity) . '~~m~~, but you only have ' . $user->getMoneys() . '~~m~~!');

        $transactionService->spendMoney($user, $bid * $quantity, 'Money put in for a bid on ' . $quantity . 'x ' . $item->getName() . '.', false);

        $myBid = (new MarketBid())
            ->setUser($user)
            ->setBid($bid)
            ->setQuantity($quantity)
            ->setItem($item)
            ->setTargetLocation($location)
        ;

        $em->persist($myBid);

        $em->flush();

        return $responseService->success($myBid, [ SerializationGroupEnum::MY_MARKET_BIDS ]);
    }

    /**
     * @Route("/{bidId}", methods={"DELETE"}, requirements={"bidId"="\d+"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function deleteBid(
        int $bidId, ResponseService $responseService, TransactionService $transactionService,
        MarketBidRepository $marketBidRepository, EntityManagerInterface $em
    )
    {
        $user = $this->getUser();
        $bid = $marketBidRepository->find($bidId);

        if(!$bid || $bid->getUser()->getId() !== $user->getId())
            throw new NotFoundHttpException('That bid could not be found (maybe someone else already sold you the item!)');

        $em->remove($bid);

        $transactionService->getMoney($user, $bid->getQuantity() * $bid->getBid(), 'Money refunded from canceling bid on ' . $bid->getQuantity() . 'x ' . $bid->getItem()->getName() . '.');

        return $responseService->success();
    }
}
