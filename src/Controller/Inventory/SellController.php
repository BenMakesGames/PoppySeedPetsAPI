<?php
declare(strict_types=1);

namespace App\Controller\Inventory;

use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotFoundException;
use App\Exceptions\PSPNotUnlockedException;
use App\Repository\InventoryRepository;
use App\Service\MarketService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/inventory")]
class SellController extends AbstractController
{
    #[Route("/sell", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function setSellPrice(
        ResponseService $responseService, Request $request, EntityManagerInterface $em, MarketService $marketService,
        InventoryRepository $inventoryRepository
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Market))
            throw new PSPNotUnlockedException('Market');

        $itemIds = $request->request->all('items');

        if(count($itemIds) === 0)
            throw new PSPFormValidationException('You must select at least one item!');

        $price = $request->request->getInt('price', 0);

        if($price > $user->getMaxSellPrice())
            throw new PSPInvalidOperationException('You cannot list items for more than ' . $user->getMaxSellPrice() . ' moneys. See the Market Manager to see if you can increase this limit!');

        /** @var Inventory[] $inventory */
        $inventory = $inventoryRepository->createQueryBuilder('i')
            ->leftJoin('i.holder', 'holder')
            ->leftJoin('i.wearer', 'wearer')
            ->leftJoin('i.lunchboxItem', 'lunchboxItem')
            ->andWhere('i.owner=:user')
            ->andWhere('i.id IN (:itemIds)')
            ->andWhere('i.lockedToOwner = 0')
            ->andWhere('holder IS NULL')
            ->andWhere('wearer IS NULL')
            ->andWhere('lunchboxItem IS NULL')
            ->setParameter('user', $user->getId())
            ->setParameter('itemIds', $itemIds)
            ->getQuery()
            ->execute()
        ;

        if(count($inventory) !== count($itemIds))
            throw new PSPNotFoundException('One or more of the selected items do not exist! Maybe reload and try again??');

        $inventoryToUpdateMinimumPriceFor = [];

        // if you're UNlisting items... EASY: do that:
        if($price <= 0)
        {
            foreach($inventory as $i)
            {
                if($i->getForSale())
                {
                    $em->remove($i->getForSale());
                    $i->setForSale(null);

                    $key = self::getInventoryKey($i);

                    if(!array_key_exists($key, $inventoryToUpdateMinimumPriceFor))
                    {
                        $inventoryToUpdateMinimumPriceFor[$key] = [
                            'item' => $i->getItem(),
                        ];
                    }
                }
            }
        }
        else
        {
            $anySoldToBidder = false;

            foreach($inventory as $i)
            {
                $soldToBidder = $marketService->sell($i, $price);

                if($soldToBidder)
                {
                    $anySoldToBidder = true;
                    $em->flush();
                }
                else
                {
                    $key = self::getInventoryKey($i);

                    if(!array_key_exists($key, $inventoryToUpdateMinimumPriceFor))
                    {
                        $inventoryToUpdateMinimumPriceFor[$key] = [
                            'item' => $i->getItem(),
                        ];
                    }
                }
            }

            if($anySoldToBidder)
                $responseService->setReloadInventory();
        }

        $em->flush();

        foreach($inventoryToUpdateMinimumPriceFor as $i)
            $marketService->updateLowestPriceForItem($i['item']);

        $em->flush();

        return $responseService->success();
    }

    private static function getInventoryKey(Inventory $inventory): string
    {
        return $inventory->getItem()->getId();
    }
}
