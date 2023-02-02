<?php
namespace App\Controller\Museum;

use App\Entity\User;
use App\Enum\LocationEnum;
use App\Functions\ArrayFunctions;
use App\Repository\ItemRepository;
use App\Service\InventoryService;
use App\Service\MuseumService;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/museum")
 */
class GiftShopController extends AbstractController
{
    /**
     * @Route("/giftShop", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
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

    /**
     * @Route("/giftShop/buy", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function buyFromGiftShop(
        Request $request, ResponseService $responseService, MuseumService $museumService,
        InventoryService $inventoryService, ItemRepository $itemRepository
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        $categoryName = $request->request->get('category', '');
        $itemName = $request->request->get('item', '');

        if(!$categoryName || !$itemName)
            throw new UnprocessableEntityHttpException('That item couldn\'t be found... reload and try again.');

        $giftShop = $museumService->getGiftShopInventory($user);

        $category = ArrayFunctions::find_one($giftShop, fn($c) => $c['category'] === $categoryName);

        if(!$category)
            throw new UnprocessableEntityHttpException('That item couldn\'t be found... reload and try again.');

        $item = ArrayFunctions::find_one($category['inventory'], fn($i) => $i['item']['name'] === $itemName);

        if(!$item)
            throw new UnprocessableEntityHttpException('That item couldn\'t be found... reload and try again.');

        $pointsRemaining = $user->getMuseumPoints() - $user->getMuseumPointsSpent();

        if($item['cost'] > $pointsRemaining)
            throw new UnprocessableEntityHttpException('That would cost ' . $item['cost'] . ' Favor, but you only have ' . $pointsRemaining . '!');

        $itemObject = $itemRepository->findOneByName($item['item']['name']);

        $itemsInBuyersHome = $inventoryService->countTotalInventory($user, LocationEnum::HOME);

        $targetLocation = LocationEnum::HOME;

        if($itemsInBuyersHome >= User::MAX_HOUSE_INVENTORY)
        {
            $itemsInBuyersBasement = $inventoryService->countTotalInventory($user, LocationEnum::BASEMENT);

            if($itemsInBuyersBasement < User::MAX_BASEMENT_INVENTORY)
                $targetLocation = LocationEnum::BASEMENT;
            else
                throw new UnprocessableEntityHttpException('There\'s not enough space in your house or basement!');
        }

        $user->addMuseumPointsSpent($item['cost']);

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
