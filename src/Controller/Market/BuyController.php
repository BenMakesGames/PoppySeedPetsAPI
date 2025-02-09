<?php
declare(strict_types=1);

namespace App\Controller\Market;

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
use App\Service\ResponseService;
use App\Service\TransactionService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/market")]
class BuyController extends AbstractController
{
    #[Route("/buy", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function buy(
        Request $request, ResponseService $responseService, CacheItemPoolInterface $cache, EntityManagerInterface $em,
        IRandom $squirrel3, TransactionService $transactionService, MarketService $marketService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        $itemId = $request->request->getInt('item', 0);
        $price = $request->request->getInt('sellPrice', 0);

        if($itemId === 0 || $price === 0)
            throw new PSPFormValidationException('Item and price are both required.');

        if(InventoryForSale::calculateBuyPrice($price) > $user->getMoneys())
            throw new PSPNotEnoughCurrencyException(InventoryForSale::calculateBuyPrice($price) . '~~m~~', $user->getMoneys() . '~~m~~');

        $itemsAtHome = InventoryRepository::countItemsInLocation($em, $user, LocationEnum::HOME);

        $placeItemsIn = LocationEnum::HOME;

        if($itemsAtHome >= User::MAX_HOUSE_INVENTORY)
        {
            if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Basement))
                throw new PSPInvalidOperationException('Your house has ' . $itemsAtHome . ' items; you\'ll need to make some space, first!');

            $itemsInBasement = InventoryRepository::countItemsInLocation($em, $user, LocationEnum::BASEMENT);

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

        $qb = $em->getRepository(InventoryForSale::class)->createQueryBuilder('i')
            ->join('i.inventory', 'inventory')
            ->andWhere('i.sellPrice<=:price')
            ->andWhere('inventory.item=:item')
            ->addOrderBy('i.sellPrice', 'ASC')
            ->addOrderBy('i.sellListDate', 'ASC')
            ->setParameter('price', $price)
            ->setParameter('item', $itemId)
        ;

        /** @var InventoryForSale[] $forSale */
        $forSale = $qb->setMaxResults(50)->getQuery()->getResult();

        if(count($forSale) === 0)
        {
            $marketService->removeMarketListingForItem($itemId);
            $em->flush();

            throw new PSPNotFoundException('An item for that price could not be found on the market. Someone may have bought it up just before you did! Sorry :| Reload the page to get the latest prices available!');
        }

        $forSale = array_filter($forSale, function(InventoryForSale $forSale) use($cache) {
            $item = $cache->getItem('Trading For Sale #' . $forSale->getId());

            return !$item->isHit();
        });

        if(count($forSale) === 0)
            throw new PSPNotFoundException('An item for that price could not be found on the market. Someone may have bought it up just before you did! Sorry :| Reload the page to get the latest prices available!');

        /** @var InventoryForSale $itemToBuy */
        $itemToBuy = ArrayFunctions::min($forSale, fn(InventoryForSale $inventory) => $inventory->getSellPrice());

        $inventory = $itemToBuy->getInventory();

        $item = $cache->getItem('Trading For Sale #' . $itemToBuy->getId());
        $item->set(true)->expiresAfter(\DateInterval::createFromDateString('1 minute'));
        $cache->save($item);

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

        $marketService->updateLowestPriceForItem(
            $inventory->getItem(),
        );

        $em->flush();

        return $responseService->success($itemToBuy, [ SerializationGroupEnum::MY_INVENTORY ]);
    }
}
