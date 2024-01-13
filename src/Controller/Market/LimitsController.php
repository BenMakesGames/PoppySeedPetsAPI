<?php
namespace App\Controller\Market;

use App\Entity\User;
use App\Enum\LocationEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotFoundException;
use App\Functions\ItemRepository;
use App\Functions\PlayerLogFactory;
use App\Service\InventoryService;
use App\Service\MarketService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/market")]
class LimitsController extends AbstractController
{
    #[Route("/limits", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getMarketLimits(ResponseService $responseService, MarketService $marketService)
    {
        /** @var User $user */
        $user = $this->getUser();

        return $responseService->success([
            'offeringBulkSellUpgrade' => $marketService->canOfferWingedKey($user),
            'limits' => [
                'moneysLimit' => $user->getMaxSellPrice(),
                'itemRequired' => $marketService->getItemToRaiseLimit($user)
            ]
        ]);
    }

    #[Route("/limits/increase", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function increaseMarketLimits(
        ResponseService $responseService, MarketService $marketService, InventoryService $inventoryService,
        EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        $itemRequired = $marketService->getItemToRaiseLimit($user);

        if(!$itemRequired)
            throw new PSPInvalidOperationException('The market limits don\'t go any higher!');

        $itemRequiredId = ItemRepository::getIdByName($em, $itemRequired['itemName']);

        if($inventoryService->loseItem($user, $itemRequiredId, [ LocationEnum::HOME, LocationEnum::BASEMENT ], 1) === 0)
            throw new PSPNotFoundException('Come back when you ACTUALLY have the item.');

        $user->setMaxSellPrice($user->getMaxSellPrice() + 10);

        PlayerLogFactory::create(
            $em,
            $user,
            'You gave ' . $itemRequired['itemName'] . ' to Argentelle to increase your maximum Market sell price to ' . $user->getMaxSellPrice() . '.',
            [ 'Market' ]
        );

        $em->flush();

        return $responseService->success([
            'moneysLimit' => $user->getMaxSellPrice(),
            'itemRequired' => $marketService->getItemToRaiseLimit($user)
        ]);
    }
}
