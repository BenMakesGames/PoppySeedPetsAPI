<?php
namespace App\Controller;

use App\Entity\Inventory;
use App\Enum\SerializationGroupEnum;
use App\Service\Filter\MarketFilterService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/market")
 */
class MarketController extends PsyPetsController
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
     * @Route("/{inventory}/buy", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function buy(
        Inventory $inventory, ResponseService $responseService, AdapterInterface $cache, EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        if($inventory->getOwner()->getId() === $user->getId())
            throw new UnprocessableEntityHttpException('You cannot buy your own items. That would be silly.');

        if($inventory->getSellPrice() === null)
            throw new UnprocessableEntityHttpException('That item does not exist.');

        if($inventory->getBuyPrice() > $user->getMoneys())
            throw new UnprocessableEntityHttpException('You do not have enough money to buy that item.');

        $item = $cache->getItem('Trading Inventory #' . $inventory->getId());

        if($item->isHit())
            throw new ConflictHttpException('This item is currently being exchanged with another player; unless the exchange failed, it is probably no longer available. Sorry :(');

        $item->set(true)->expiresAfter(\DateInterval::createFromDateString('5 minutes'));
        $cache->save($item);

        try
        {
            $inventory->getOwner()->increaseMoneys($inventory->getSellPrice());
            $user->increaseMoneys(-$inventory->getBuyPrice());

            $inventory
                ->setOwner($user)
                ->setSellPrice(null)
            ;

            if($inventory->getPet())
                $inventory->getPet()->setTool(null);

            $em->flush();
        }
        finally
        {
            $cache->deleteItem('Trading Inventory #' . $inventory->getId());
        }

        return $responseService->success();
    }
}