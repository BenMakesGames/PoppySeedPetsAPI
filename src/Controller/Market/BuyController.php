<?php
namespace App\Controller\Market;

use App\Entity\Inventory;
use App\Entity\InventoryForSale;
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
use App\Service\PerformanceProfiler;
use App\Service\ResponseService;
use App\Service\TransactionService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/market")]
class BuyController extends AbstractController
{
    #[Route("/buy", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function buy(
        Request $request, ResponseService $responseService, CacheItemPoolInterface $cache, EntityManagerInterface $em,
        IRandom $squirrel3, TransactionService $transactionService, PerformanceProfiler $performanceProfiler,
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

        $isFluff = $itemId == 34;

        if(InventoryForSale::calculateBuyPrice($price) > $user->getMoneys())
            throw new PSPNotEnoughCurrencyException(InventoryForSale::calculateBuyPrice($price) . '~~m~~', $user->getMoneys() . '~~m~~');

        $time = microtime(true);
        $itemsAtHome = InventoryRepository::countItemsInLocation($em, $user, LocationEnum::HOME);
        $performanceProfiler->logExecutionTime(__METHOD__ . ' - Buying Fluff, counting items at home', microtime(true) - $time);

        $placeItemsIn = LocationEnum::HOME;

        if($itemsAtHome >= User::MAX_HOUSE_INVENTORY)
        {
            if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Basement))
                throw new PSPInvalidOperationException('Your house has ' . $itemsAtHome . ' items; you\'ll need to make some space, first!');

            $time = microtime(true);
            $itemsInBasement = InventoryRepository::countItemsInLocation($em, $user, LocationEnum::BASEMENT);
            $performanceProfiler->logExecutionTime(__METHOD__ . ' - Buying Fluff, counting items in basement', microtime(true) - $time);

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

        $time = microtime(true);

        $qb = $em->getRepository(InventoryForSale::class)->createQueryBuilder('i')
            ->join('i.inventory', 'inventory')
            ->andWhere('i.sellPrice<=:price')
            ->andWhere('inventory.item=:item')
            ->addOrderBy('i.sellPrice', 'ASC')
            ->addOrderBy('i.sellListDate', 'ASC')
            ->setParameter('price', $price)
            ->setParameter('item', $itemId)
        ;

        if($bonusId)
        {
            $qb = $qb
                ->andWhere('inventory.enchantment=:bonusId')
                ->setParameter('bonusId', $bonusId)
            ;
        }
        else
        {
            $qb = $qb->andWhere('inventory.enchantment IS NULL');
        }

        if($spiceId)
        {
            $qb = $qb
                ->andWhere('inventory.spice=:spiceId')
                ->setParameter('spiceId', $spiceId)
            ;
        }
        else
        {
            $qb = $qb->andWhere('inventory.spice IS NULL');
        }

        /** @var InventoryForSale[] $forSale */
        $forSale = $qb->setMaxResults(50)->getQuery()->getResult();

        if(count($forSale) === 0)
        {
            $marketService->removeMarketListingForItem($itemId, $bonusId, $spiceId);
            $em->flush();

            throw new PSPNotFoundException('An item for that price could not be found on the market. Someone may have bought it up just before you did! Sorry :| Reload the page to get the latest prices available!');
        }

        $performanceProfiler->logExecutionTime(__METHOD__ . ' - Buying Fluff, fetching up to 50 for sale', microtime(true) - $time);

        $time = microtime(true);

        $forSale = array_filter($forSale, function(InventoryForSale $forSale) use($cache) {
            $item = $cache->getItem('Trading For Sale #' . $forSale->getId());

            return !$item->isHit();
        });

        $performanceProfiler->logExecutionTime(__METHOD__ . ' - Buying Fluff, checking for sale locks', microtime(true) - $time);

        if(count($forSale) === 0)
            throw new PSPNotFoundException('An item for that price could not be found on the market. Someone may have bought it up just before you did! Sorry :| Reload the page to get the latest prices available!');

        /** @var InventoryForSale $itemToBuy */
        $itemToBuy = ArrayFunctions::min($forSale, fn(InventoryForSale $inventory) => $inventory->getSellPrice());

        $inventory = $itemToBuy->getInventory();

        $item = $cache->getItem('Trading For Sale #' . $itemToBuy->getId());
        $item->set(true)->expiresAfter(\DateInterval::createFromDateString('1 minute'));
        $cache->save($item);

        $time = microtime(true);

        try
        {
            if($inventory->getOwner() === $user)
            {
                if($inventory->getLocation() === LocationEnum::BASEMENT)
                    $responseService->addFlashMessage('The ' . $inventory->getItem()->getName() . ' is one of yours! It has been removed from the Market, and can be found in your Basement!');
                else
                    $responseService->addFlashMessage('The ' . $inventory->getItem()->getName() . ' is one of yours! It has been removed from the Market, and can be found in your Home!');
            }
            else
            {
                $transactionService->getMoney($inventory->getOwner(), $itemToBuy->getSellPrice(), 'Sold ' . InventoryModifierFunctions::getNameWithModifiers($inventory) . ' in the Market.', [ 'Market' ]);

                $transactionService->spendMoney($user, $itemToBuy->getBuyPrice(), 'Bought ' . InventoryModifierFunctions::getNameWithModifiers($inventory) . ' in the Market.', true, [ 'Market' ]);

                $marketService->logExchange($inventory, $itemToBuy->getBuyPrice());

                $marketService->transferItemToPlayer($inventory, $user, $placeItemsIn, $itemToBuy->getSellPrice(), $user->getName() . ' bought this from ' . $inventory->getOwner()->getName() . ' at the Market.');

                if($placeItemsIn === LocationEnum::BASEMENT)
                    $responseService->addFlashMessage('The ' . $inventory->getItem()->getName() . ' is yours; you\'ll find it in your Basement! (Your Home is a bit full...)');
                else
                    $responseService->addFlashMessage('The ' . $inventory->getItem()->getName() . ' is yours!');
            }

            $em->remove($itemToBuy);

            $em->flush();
        }
        finally
        {
            $cache->deleteItem('Trading For Sale #' . $itemToBuy->getId());
        }

        $performanceProfiler->logExecutionTime(__METHOD__ . ' - Buying Fluff, transferring the item between players', microtime(true) - $time);

        $time = microtime(true);

        $marketService->updateLowestPriceForItem(
            $inventory->getItem(),
            $inventory->getEnchantment(),
            $inventory->getSpice()
        );

        $em->flush();

        $performanceProfiler->logExecutionTime(__METHOD__ . ' - Buying Fluff, updating lowest price', microtime(true) - $time);

        return $responseService->success($itemToBuy, [ SerializationGroupEnum::MY_INVENTORY ]);
    }
}
