<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Enum\LocationEnum;
use App\Enum\UserStatEnum;
use App\Model\TraderOffer;
use App\Model\TraderOfferCostOrYield;
use App\Repository\ItemRepository;
use App\Repository\PetRepository;
use App\Repository\UserStatsRepository;
use App\Service\HouseService;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\TraderService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/sharuminyinka")
 */
class SharuminyinkaController extends PoppySeedPetsItemController
{
    /**
     * @Route("/{inventory}/createHope", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function createHope(
        Inventory $inventory, ResponseService $responseService, TraderService $traderService, ItemRepository $itemRepository,
        EntityManagerInterface $em
    )
    {
        $this->validateInventory($inventory, 'sharuminyinka/#/createHope');

        $user = $this->getUser();

        $exchange = new TraderOffer(
            [
                TraderOfferCostOrYield::createItem($itemRepository->findOneByName('Poker'), 1),
                TraderOfferCostOrYield::createItem($itemRepository->findOneByName('Spider'), 1),
                TraderOfferCostOrYield::createItem($itemRepository->findOneByName('Feathers'), 1),
                TraderOfferCostOrYield::createItem($itemRepository->findOneByName('Quintessence'), 1),
            ],
            [
                TraderOfferCostOrYield::createItem($itemRepository->findOneByName('Sharuminyinka\'s Hope'), 1),
            ],
            ''
        );

        if(!$traderService->userCanMakeExchange($user, $exchange))
        {
            return $responseService->itemActionSuccess('You need a Poker, Spider, Feathers, and Quintessence to do this.');
        }
        else
        {
            $traderService->makeExchange($user, $exchange, $user->getName() . ' made this, using ' . $inventory->getItem()->getName() . '.');

            $em->flush();

            return $responseService->itemActionSuccess('You created Sharuminyinka\'s Hope.', [ 'reloadInventory' => true ]);
        }
    }
    /**
     * @Route("/{inventory}/createMemory", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function createMemory(
        Inventory $inventory, ResponseService $responseService, TraderService $traderService, ItemRepository $itemRepository,
        EntityManagerInterface $em
    )
    {
        $this->validateInventory($inventory, 'sharuminyinka/#/createMemory');

        $user = $this->getUser();

        $exchange = new TraderOffer(
            [
                TraderOfferCostOrYield::createItem($itemRepository->findOneByName('Crazy-hot Torch'), 1),
                TraderOfferCostOrYield::createItem($itemRepository->findOneByName('Blackonite'), 1),
                TraderOfferCostOrYield::createItem($itemRepository->findOneByName('String'), 1),
                TraderOfferCostOrYield::createItem($itemRepository->findOneByName('Quintessence'), 1),
            ],
            [
                TraderOfferCostOrYield::createItem($itemRepository->findOneByName('Tig\'s Memory'), 1),
            ],
            ''
        );

        if(!$traderService->userCanMakeExchange($user, $exchange))
        {
            return $responseService->itemActionSuccess('You need a Crazy-hot Torch, Blackonite, String, and Quintessence to do this.');
        }
        else
        {
            $traderService->makeExchange($user, $exchange, $user->getName() . ' made this, using ' . $inventory->getItem()->getName() . '.');

            $em->flush();

            return $responseService->itemActionSuccess('You created Tig\'s Memory.', [ 'reloadInventory' => true ]);
        }
    }
}
