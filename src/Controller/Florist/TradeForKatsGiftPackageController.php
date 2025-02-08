<?php
declare(strict_types=1);

namespace App\Controller\Florist;

use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPNotEnoughCurrencyException;
use App\Exceptions\PSPNotUnlockedException;
use App\Functions\ArrayFunctions;
use App\Functions\ItemRepository;
use App\Model\TraderOffer;
use App\Model\TraderOfferCostOrYield;
use App\Repository\InventoryRepository;
use App\Service\FloristService;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\TraderService;
use App\Service\TransactionService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/florist")]
class TradeForKatsGiftPackageController extends AbstractController
{
    #[Route("/tradeForGiftPackage", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function makeTrade(
        InventoryRepository $inventoryRepository, ResponseService $responseService,
        EntityManagerInterface $em, TraderService $traderService, UserStatsService $userStatsService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Florist))
            throw new PSPNotUnlockedException('Florist');

        $quantities = $inventoryRepository->getInventoryQuantities($user, LocationEnum::HOME, 'name');

        $exchange = TraderOffer::createTradeOffer(
            [
                TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($em, 'Monday Coin'), 1),
                TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($em, 'Tuesday Coin'), 1),
                TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($em, 'Wednesday Coin'), 1),
                TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($em, 'Thursday Coin'), 1),
                TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($em, 'Friday Coin'), 1),
                TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($em, 'Saturday Coin'), 1),
                TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($em, 'Sunday Coin'), 1),
            ],
            [
                TraderOfferCostOrYield::createItem(ItemRepository::findOneByName($em, 'Kat\'s Gift Package'), 1),
            ],
            '',
            $user,
            $quantities,
            true
        );

        $traderService->makeExchange($user, $exchange, LocationEnum::HOME, 1, 'Received by trading with the florist, Kat.');
        $userStatsService->incrementStat($user, 'Traded for Kat\'s Gift Package');

        $em->flush();

        return $responseService->success();
    }
}
