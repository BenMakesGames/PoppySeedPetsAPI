<?php
namespace App\Controller\Museum;

use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotEnoughCurrencyException;
use App\Exceptions\PSPNotFoundException;
use App\Functions\ArrayFunctions;
use App\Functions\ItemRepository;
use App\Service\InventoryService;
use App\Service\MuseumService;
use App\Service\ResponseService;
use App\Service\TransactionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/museum")]
class GiftShopController extends AbstractController
{
    #[Route("/giftShop", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getGiftShop(ResponseService $responseService, MuseumService $museumService)
    {
        /** @var User $user */
        $user = $this->getUser();

        $giftShop = $museumService->getGiftShopInventory($user);

        return $responseService->success([
            'pointsAvailable' => $user->getMuseumPoints() - $user->getMuseumPointsSpent(),
            'giftShop' => $giftShop
        ]);
    }

    #[Route("/giftShop/buy", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function buyFromGiftShop(
        Request $request, ResponseService $responseService, MuseumService $museumService,
        InventoryService $inventoryService, EntityManagerInterface $em, TransactionService $transactionService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        $categoryName = $request->request->get('category', '');
        $itemName = $request->request->get('item', '');

        if(!$categoryName || !$itemName)
            throw new PSPNotFoundException('That item couldn\'t be found... reload and try again.');

        $giftShop = $museumService->getGiftShopInventory($user);

        $category = ArrayFunctions::find_one($giftShop, fn($c) => $c['category'] === $categoryName);

        if(!$category)
            throw new PSPNotFoundException('That item couldn\'t be found... reload and try again.');

        $item = ArrayFunctions::find_one($category['inventory'], fn($i) => $i['item']['name'] === $itemName);

        if(!$item)
            throw new PSPNotFoundException('That item couldn\'t be found... reload and try again.');

        $pointsRemaining = $user->getMuseumPoints() - $user->getMuseumPointsSpent();

        if($item['cost'] > $pointsRemaining)
            throw new PSPNotEnoughCurrencyException($item['cost'] . ' Favor', $pointsRemaining);

        $itemObject = ItemRepository::findOneByName($em, $item['item']['name']);

        $itemsInBuyersHome = InventoryService::countTotalInventory($em, $user, LocationEnum::HOME);

        $targetLocation = LocationEnum::HOME;

        if($itemsInBuyersHome >= User::MAX_HOUSE_INVENTORY)
        {
            if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Basement))
                throw new PSPInvalidOperationException('There\'s not enough space in your house!');

            $itemsInBuyersBasement = InventoryService::countTotalInventory($em, $user, LocationEnum::BASEMENT);

            if($itemsInBuyersBasement < User::MAX_BASEMENT_INVENTORY)
                $targetLocation = LocationEnum::BASEMENT;
            else
                throw new PSPInvalidOperationException('There\'s not enough space in your house or basement!');
        }

        $transactionService->spendMuseumFavor($user, $item['cost'], 'You bought ' . $itemObject->getNameWithArticle() . ' from the Museum Gift Shop.');

        $inventoryService->receiveItem($itemObject, $user, null, $user->getName() . ' bought this from the Museum Gift Shop.', $targetLocation, true);

        if($targetLocation === LocationEnum::BASEMENT)
            $responseService->addFlashMessage('You bought ' . $itemObject->getNameWithArticle() . '; your house is full, so it\'s been sent to your basement.');
        else
            $responseService->addFlashMessage('You bought ' . $itemObject->getNameWithArticle() . '.');

        return $responseService->success([
            'pointsAvailable' => $user->getMuseumPoints() - $user->getMuseumPointsSpent(),
            'giftShop' => $giftShop
        ]);
    }
}
