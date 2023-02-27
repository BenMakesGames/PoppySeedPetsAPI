<?php
namespace App\Controller\Inventory;

use App\Entity\Inventory;
use App\Entity\User;
use App\Repository\InventoryRepository;
use App\Service\MarketService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/inventory")
 */
class SellController extends AbstractController
{
    /**
     * @Route("/sell", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function setSellPrice(
        ResponseService $responseService, Request $request, EntityManagerInterface $em, MarketService $marketService,
        InventoryRepository $inventoryRepository
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        if($user->getUnlockedMarket() === null)
            throw new AccessDeniedHttpException('You have not yet unlocked this feature.');

        $itemIds = $request->request->get('items', []);

        if(!is_array($itemIds))
        {
            if(!is_numeric($itemIds))
                throw new UnprocessableEntityHttpException('You must select at least one item!');

            $itemIds = [ $itemIds ];
        }

        if(count($itemIds) === 0)
            throw new UnprocessableEntityHttpException('You must select at least one item!');

        $price = $request->request->getInt('price', 0);

        if($price > $user->getMaxSellPrice())
            throw new UnprocessableEntityHttpException('You cannot list items for more than ' . $user->getMaxSellPrice() . ' moneys. See the Market Manager to see if you can increase this limit!');

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
            throw new UnprocessableEntityHttpException('One or more of the selected items do not exist! Maybe reload and try again??');

        // if you're UNlisting items... EASY: do that:
        if($price <= 0)
        {
            foreach($inventory as $i)
                $i->setSellPrice(null);

            $em->flush();

            return $responseService->success();
        }

        $anySoldToBidder = false;

        $inventoryToUpdateMinimumPriceFor = [];

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
                $key =
                    $i->getItem()->getId() . ',' .
                    ($i->getEnchantment() ? $i->getEnchantment()->getId() : 'null') . ','.
                    ($i->getSpice() ? $i->getSpice()->getId() : 'null')
                ;

                if(!array_key_exists($key, $inventoryToUpdateMinimumPriceFor))
                {
                    $inventoryToUpdateMinimumPriceFor[$key] = [
                        'item' => $i->getItem(),
                        'enchantment' => $i->getEnchantment(),
                        'spice' => $i->getSpice()
                    ];
                }
            }
        }

        if($anySoldToBidder)
            $responseService->setReloadInventory();

        $em->flush();

        foreach($inventoryToUpdateMinimumPriceFor as $i)
            $marketService->updateLowestPriceForItem($i['item'], $i['enchantment'], $i['spice']);

        $em->flush();

        return $responseService->success();
    }
}
