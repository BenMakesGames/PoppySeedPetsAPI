<?php
namespace App\Controller\Market;

use App\Entity\DailyMarketItemAverage;
use App\Entity\Item;
use App\Enum\SerializationGroupEnum;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/market")
 */
class ItemHistoryController extends AbstractController
{
    /**
     * @Route("/history/{item}", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getItemHistory(
        ResponseService $responseService, EntityManagerInterface $em,

        Item $item
    )
    {
        $maxAge = \DateInterval::createFromDateString('7 days');

        $itemHistory = $em->getRepository(DailyMarketItemAverage::class)->createQueryBuilder('h')
            ->andWhere('h.item=:item')
            ->andWhere('h.date>=:earliestDate')
            ->setParameter('item', $item)
            ->setParameter('earliestDate', (new \DateTimeImmutable())->sub($maxAge)->format('Y-m-d'))
            ->getQuery()
            ->execute()
        ;

        $lastHistoryItem = $em->getRepository(DailyMarketItemAverage::class)->findOneBy([ 'item' => $item ], [ 'date' => 'DESC' ]);

        return $responseService->success(
            [
                'history' => $itemHistory,
                'lastHistory' => $lastHistoryItem
            ],
            [ SerializationGroupEnum::MARKET_ITEM_HISTORY ]
        );
    }
}
