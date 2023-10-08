<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Functions\ItemRepository;
use App\Model\TraderOffer;
use App\Model\TraderOfferCostOrYield;
use App\Repository\InventoryRepository;
use App\Service\ResponseService;
use App\Service\TraderService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/sharuminyinka")
 */
class SharuminyinkaController extends AbstractController
{
    /**
     * @Route("/{inventory}/createHope", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function createHope(
        Inventory $inventory, ResponseService $responseService, TraderService $traderService,
        EntityManagerInterface $em, InventoryRepository $inventoryRepository
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'sharuminyinka/#/createHope');

        $houseInventoryQuantities = $inventoryRepository->getInventoryQuantities($user, LocationEnum::HOME, 'name');

        $exchange = TraderOffer::createTradeOffer(
            [
                TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($em, 'Poker'), 1),
                TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($em, 'Spider'), 1),
                TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($em, 'Feathers'), 1),
                TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($em, 'Quintessence'), 1),
            ],
            [
                TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($em, 'Sharuminyinka\'s Hope'), 1),
            ],
            '',
            $user,
            $houseInventoryQuantities
        );

        if(!$traderService->userCanMakeExchange($user, $exchange))
        {
            return $responseService->itemActionSuccess('You need a Poker, Spider, Feathers, and Quintessence to do this.');
        }
        else
        {
            $traderService->makeExchange($user, $exchange, 1, $user->getName() . ' made this, using ' . $inventory->getItem()->getName() . '.');

            $em->flush();

            return $responseService->itemActionSuccess('You created Sharuminyinka\'s Hope.');
        }
    }
    /**
     * @Route("/{inventory}/createMemory", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function createMemory(
        Inventory $inventory, ResponseService $responseService, TraderService $traderService,
        EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'sharuminyinka/#/createMemory');

        $exchange = TraderOffer::createTradeOffer(
            [
                TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($em, 'Crazy-hot Torch'), 1),
                TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($em, 'Blackonite'), 1),
                TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($em, 'String'), 1),
                TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($em, 'Quintessence'), 1),
            ],
            [
                TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($em, 'Tig\'s Memory'), 1),
            ],
            '',
            $user,
            []
        );

        if(!$traderService->userCanMakeExchange($user, $exchange))
        {
            return $responseService->itemActionSuccess('You need a Crazy-hot Torch, Blackonite, String, and Quintessence to do this.');
        }
        else
        {
            $traderService->makeExchange($user, $exchange, 1, $user->getName() . ' made this, using ' . $inventory->getItem()->getName() . '.');

            $em->flush();

            return $responseService->itemActionSuccess('You created Tig\'s Memory.');
        }
    }
}
