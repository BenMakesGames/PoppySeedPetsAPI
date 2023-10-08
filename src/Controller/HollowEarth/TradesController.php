<?php
namespace App\Controller\HollowEarth;

use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotFoundException;
use App\Functions\ItemRepository;
use App\Service\HollowEarthService;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/hollowEarth")
 */
class TradesController extends AbstractController
{
    /**
     * @Route("/trades", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getTrades(ResponseService $responseService, HollowEarthService $hollowEarthService)
    {
        $user = $this->getUser();
        $player = $user->getHollowEarthPlayer();
        $tile = $player->getCurrentTile();

        if(!$tile || !$tile->getIsTradingDepot())
            throw new PSPInvalidOperationException('You are not on a trade depot!');

        if($player->getCurrentAction() || $player->getMovesRemaining() > 0)
            throw new PSPInvalidOperationException('You can\'t trade while you\'re moving!');

        $trades = $hollowEarthService->getTrades($player);

        return $responseService->success($trades);
    }

    /**
     * @Route("/trades/{tradeId}/exchange", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function makeExchange(
        string $tradeId, Request $request, ResponseService $responseService, EntityManagerInterface $em,
        HollowEarthService $hollowEarthService, InventoryService $inventoryService
    )
    {
        /** @var User $user */
        $user = $this->getUser();
        $player = $user->getHollowEarthPlayer();
        $tile = $player->getCurrentTile();

        if(!$tile || !$tile->getIsTradingDepot())
            throw new PSPInvalidOperationException('You are not on a trade depot!');

        if($player->getCurrentAction() || $player->getMovesRemaining() > 0)
            throw new PSPInvalidOperationException('You can\'t trade while you\'re moving!');

        $trade = $hollowEarthService->getTrade($player, $tradeId);

        if(!$trade)
            throw new PSPNotFoundException('No such trade exists...');

        $quantity = $request->request->getInt('quantity', 1);

        if($trade['maxQuantity'] < $quantity)
            throw new PSPInvalidOperationException('You do not have enough goods to make ' . $quantity . ' trade' . ($quantity == 1 ? '' : 's') . '; you can do up to ' . $trade['maxQuantity'] . ', at most.');

        $itemsAtHome = $inventoryService->countTotalInventory($user, LocationEnum::HOME);

        $destination = LocationEnum::HOME;

        if($itemsAtHome + $quantity > User::MAX_HOUSE_INVENTORY)
        {
            if($user->hasUnlockedFeature(UnlockableFeatureEnum::Basement))
            {
                $destination = LocationEnum::BASEMENT;

                $itemsInBasement = $inventoryService->countTotalInventory($user, LocationEnum::BASEMENT);

                if($itemsInBasement + $quantity > User::MAX_BASEMENT_INVENTORY)
                {
                    throw new PSPInvalidOperationException('There is not enough room in your house or basement for ' . $quantity . ' more items!');
                }
            }
            else
            {
                throw new PSPInvalidOperationException('There is not enough room in your house for ' . $quantity . ' more items!');
            }
        }

        $item = ItemRepository::findOneByName($em, $trade['item']['name']);

        if(!$item)
            throw new \Exception('No item called "' . $trade['item']['name'] . '" exists in the database!');

        for($i = 0; $i < $quantity; $i++)
            $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' traded for this in the Portal.', $destination);

        if(array_key_exists('jade', $trade['cost'])) $player->increaseJade(-$trade['cost']['jade'] * $quantity);
        if(array_key_exists('incense', $trade['cost'])) $player->increaseIncense(-$trade['cost']['incense'] * $quantity);
        if(array_key_exists('amber', $trade['cost'])) $player->increaseAmber(-$trade['cost']['amber'] * $quantity);
        if(array_key_exists('salt', $trade['cost'])) $player->increaseSalt(-$trade['cost']['salt'] * $quantity);
        if(array_key_exists('fruit', $trade['cost'])) $player->increaseFruit(-$trade['cost']['fruit'] * $quantity);

        $em->flush();

        $trades = $hollowEarthService->getTrades($player);

        $destinationDescription = $destination === LocationEnum::HOME
            ? 'your house'
            : 'your basement'
        ;

        $themOrIt = $quantity === 1 ? 'it' : 'them';

        $responseService->addFlashMessage('You traded for ' . $quantity . '× ' . $item->getName() . '. (Find ' . $themOrIt . ' in ' . $destinationDescription . '.)');

        return $responseService->success($trades);
    }
}
