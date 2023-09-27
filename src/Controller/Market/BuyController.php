<?php
namespace App\Controller\Market;

use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotEnoughCurrencyException;
use App\Exceptions\PSPNotFoundException;
use App\Functions\ArrayFunctions;
use App\Functions\InventoryModifierFunctions;
use App\Repository\InventoryRepository;
use App\Service\IRandom;
use App\Service\MarketService;
use App\Service\ResponseService;
use App\Service\TransactionService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/market")
 */
class BuyController extends AbstractController
{
    /**
     * @Route("/buy", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function buy(
        Request $request, ResponseService $responseService, CacheItemPoolInterface $cache, EntityManagerInterface $em,
        InventoryRepository $inventoryRepository, IRandom $squirrel3, TransactionService $transactionService,
        MarketService $marketService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        $itemId = $request->request->getInt('item', 0);
        $bonusId = $request->request->getInt('bonus', 0);
        $spiceId = $request->request->getInt('spice', 0);
        $price = $request->request->getInt('sellPrice', 0);

        if($itemId === 0 || $price === 0)
            throw new PSPFormValidationException('Item and price are both required.');

        if(Inventory::calculateBuyPrice($price) > $user->getMoneys())
            throw new PSPNotEnoughCurrencyException(Inventory::calculateBuyPrice($price) . '~~m~~', $user->getMoneys() . '~~m~~');

        $itemsAtHome = $inventoryRepository->countItemsInLocation($user, LocationEnum::HOME);
        $placeItemsIn = LocationEnum::HOME;

        if($itemsAtHome >= User::MAX_HOUSE_INVENTORY)
        {
            if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Basement))
                throw new PSPInvalidOperationException('Your house has ' . $itemsAtHome . ' items; you\'ll need to make some space, first!');

            $itemsInBasement = $inventoryRepository->countItemsInLocation($user, LocationEnum::BASEMENT);

            $dang = $squirrel3->rngNextFromArray([
                'Dang!',
                'Goodness!',
                'Great googly-moogly!',
                'Whaaaat?!',
            ]);

            if($itemsInBasement >= User::MAX_BASEMENT_INVENTORY)
                throw new PSPInvalidOperationException('Your house has ' . $itemsAtHome . ', and your basement has ' . $itemsInBasement . ' items! (' . $dang . ') You\'ll need to make some space, first...');

            $placeItemsIn = LocationEnum::BASEMENT;
        }

        $qb = $inventoryRepository->createQueryBuilder('i')
            ->andWhere('i.sellPrice<=:price')
            ->andWhere('i.item=:item')
            ->addOrderBy('i.sellPrice', 'ASC')
            ->addOrderBy('i.sellListDate', 'ASC')
            ->setParameter('price', $price)
            ->setParameter('item', $itemId)
        ;

        if($bonusId)
        {
            $qb = $qb
                ->andWhere('i.enchantment=:bonusId')
                ->setParameter('bonusId', $bonusId)
            ;
        }
        else
        {
            $qb = $qb->andWhere('i.enchantment IS NULL');
        }

        if($spiceId)
        {
            $qb = $qb
                ->andWhere('i.spice=:spiceId')
                ->setParameter('spiceId', $spiceId)
            ;
        }
        else
        {
            $qb = $qb->andWhere('i.spice IS NULL');
        }

        /** @var Inventory[] $forSale */
        $forSale = $qb->getQuery()->getResult();

        $forSale = array_filter($forSale, function(Inventory $inventory) use($cache) {
            $item = $cache->getItem('Trading Inventory #' . $inventory->getId());

            return !$item->isHit();
        });

        if(count($forSale) === 0)
        {
            $marketService->removeMarketListingForItem($itemId, $bonusId, $spiceId);
            $em->flush();

            throw new PSPNotFoundException('An item for that price could not be found on the market. Someone may have bought it up just before you did! Sorry :| Reload the page to get the latest prices available!');
        }

        /** @var Inventory $itemToBuy */
        $itemToBuy = ArrayFunctions::min($forSale, fn(Inventory $inventory) => $inventory->getSellPrice());

        $item = $cache->getItem('Trading Inventory #' . $itemToBuy->getId());
        $item->set(true)->expiresAfter(\DateInterval::createFromDateString('1 minute'));
        $cache->save($item);

        try
        {
            if($itemToBuy->getOwner() === $user)
            {
                $itemToBuy->setSellPrice(null);

                if($itemToBuy->getLocation() === LocationEnum::BASEMENT)
                    $responseService->addFlashMessage('The ' . $itemToBuy->getItem()->getName() . ' is one of yours! It has been removed from the Market, and can be found in your Basement!');
                else
                    $responseService->addFlashMessage('The ' . $itemToBuy->getItem()->getName() . ' is one of yours! It has been removed from the Market, and can be found in your Home!');
            }
            else
            {
                $transactionService->getMoney($itemToBuy->getOwner(), $itemToBuy->getSellPrice(), 'Sold ' . InventoryModifierFunctions::getNameWithModifiers($itemToBuy) . ' in the Market.', [ 'Market' ]);

                $transactionService->spendMoney($user, $itemToBuy->getBuyPrice(), 'Bought ' . InventoryModifierFunctions::getNameWithModifiers($itemToBuy) . ' in the Market.', true, [ 'Market' ]);

                $marketService->logExchange($itemToBuy, $itemToBuy->getBuyPrice());

                $marketService->transferItemToPlayer($itemToBuy, $user, $placeItemsIn, $itemToBuy->getSellPrice());

                if($placeItemsIn === LocationEnum::BASEMENT)
                    $responseService->addFlashMessage('The ' . $itemToBuy->getItem()->getName() . ' is yours; you\'ll find it in your Basement! (Your Home is a bit full...)');
                else
                    $responseService->addFlashMessage('The ' . $itemToBuy->getItem()->getName() . ' is yours!');
            }

            $em->flush();
        }
        finally
        {
            $cache->deleteItem('Trading Inventory #' . $itemToBuy->getId());
        }

        $marketService->updateLowestPriceForInventory($itemToBuy);
        $em->flush();

        return $responseService->success($itemToBuy, [ SerializationGroupEnum::MY_INVENTORY ]);
    }
}
