<?php
namespace App\Controller;

use App\Entity\Inventory;
use App\Enum\LocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Enum\UserStatEnum;
use App\Repository\InventoryRepository;
use App\Repository\UserStatsRepository;
use App\Service\Filter\MarketFilterService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/market")
 */
class MarketController extends PoppySeedPetsController
{
    /**
     * @Route("/search", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function search(Request $request, ResponseService $responseService, MarketFilterService $marketFilterService)
    {
        $marketFilterService->setUser($this->getUser());

        return $responseService->success(
            $marketFilterService->getResults($request->query),
            [ SerializationGroupEnum::FILTER_RESULTS, SerializationGroupEnum::MARKET_ITEM ]
        );
    }

    /**
     * @Route("/buy", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function buy(
        Request $request, ResponseService $responseService, AdapterInterface $cache, EntityManagerInterface $em,
        UserStatsRepository $userStatsRepository, InventoryRepository $inventoryRepository
    )
    {
        $user = $this->getUser();

        $itemId = $request->request->getInt('item', 0);
        $price = $request->request->getInt('sellPrice', 0);

        if($itemId === 0 || $price === 0)
            throw new UnprocessableEntityHttpException('Item and price are both required.');

        if(Inventory::calculateBuyPrice($price) > $user->getMoneys())
            throw new UnprocessableEntityHttpException('You do not have enough moneys.');

        /** @var Inventory[] $forSale */
        $forSale = $inventoryRepository->createQueryBuilder('i')
            ->andWhere('i.owner!=:user')
            ->andWhere('i.sellPrice=:price')
            ->andWhere('i.item=:item')
            ->addOrderBy('i.sellListDate', 'ASC')
            ->setParameter('user', $user->getId())
            ->setParameter('price', $price)
            ->setParameter('item', $itemId)
            ->getQuery()
            ->getResult()
        ;

        $buy = null;

        foreach($forSale as $inventory)
        {
            $item = $cache->getItem('Trading Inventory #' . $inventory->getId());

            if($item->isHit())
                continue;
            else
            {
                $item->set(true)->expiresAfter(\DateInterval::createFromDateString('2 minutes'));
                $cache->save($item);
                $buy = $inventory;
                break;
            }
        }

        if($buy === null)
            throw new NotFoundHttpException('An item for that price could not be found on the market. Someone may have bought it up just for you did! Sorry :|');

        try
        {
            $buy->getOwner()->increaseMoneys($buy->getSellPrice());
            $userStatsRepository->incrementStat($buy->getOwner(), UserStatEnum::TOTAL_MONEYS_EARNED_IN_MARKET, $buy->getSellPrice());
            $userStatsRepository->incrementStat($buy->getOwner(), UserStatEnum::ITEMS_SOLD_IN_MARKET, 1);

            $user->increaseMoneys(-$buy->getBuyPrice());
            $userStatsRepository->incrementStat($user, UserStatEnum::TOTAL_MONEYS_SPENT, $buy->getBuyPrice());
            $userStatsRepository->incrementStat($user, UserStatEnum::ITEMS_BOUGHT_IN_MARKET, 1);

            $buy
                ->setOwner($user)
                ->setSellPrice(null)
                ->setLocation(LocationEnum::HOME)
                ->setModifiedOn()
            ;

            if($buy->getHolder())
                $buy->getHolder()->setTool(null);

            if($buy->getWearer())
                $buy->getWearer()->setHat(null);

            $em->flush();
        }
        finally
        {
            $cache->deleteItem('Trading Inventory #' . $buy->getId());
        }

        return $responseService->success();
    }
}