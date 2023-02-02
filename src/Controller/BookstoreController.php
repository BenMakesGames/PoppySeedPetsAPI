<?php
namespace App\Controller;

use App\Entity\Item;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Service\BookstoreService;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\TransactionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

// allows player to buy books; inventory grows based on various criteria

/**
 * @Route("/bookstore")
 */
class BookstoreController extends AbstractController
{
    /**
     * @Route("", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getAvailableBooks(
        BookstoreService $bookstoreService, ResponseService $responseService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        if($user->getUnlockedBookstore() === null)
            throw new AccessDeniedHttpException('You have not unlocked this feature yet.');

        $data = $bookstoreService->getResponseData($user);

        return $responseService->success($data, [ SerializationGroupEnum::MARKET_ITEM ]);
    }

    /**
     * @Route("/giveItem/{item}", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function giveItem(
        string $item, BookstoreService $bookstoreService, ResponseService $responseService, EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        if($user->getUnlockedBookstore() === null)
            throw new AccessDeniedHttpException('You have not unlocked this feature yet.');

        $bookstoreService->advanceBookstoreQuest($user, $item);

        $em->flush();

        $data = $bookstoreService->getResponseData($user);

        $responseService->addFlashMessage('Thanks! Renaming Scrolls now cost ' . $bookstoreService->getRenamingScrollCost($user) . '~~m~~!');

        return $responseService->success($data, [ SerializationGroupEnum::MARKET_ITEM ]);
    }

    /**
     * @Route("/{item}/buy", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function buyBook(
        Item $item, BookstoreService $bookstoreService, InventoryService $inventoryService, EntityManagerInterface $em,
        ResponseService $responseService, TransactionService $transactionService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        if($user->getUnlockedBookstore() === null)
            throw new AccessDeniedHttpException('You have not unlocked this feature yet.');

        $bookPrices = $bookstoreService->getAvailableBooks($user);
        $gamePrices = $bookstoreService->getAvailableGames($user);
        $cafePrices = $bookstoreService->getAvailableCafe($user);

        $allPrices = array_merge($bookPrices, $gamePrices, $cafePrices);

        if(!array_key_exists($item->getName(), $allPrices))
            throw new UnprocessableEntityHttpException('That item cannot be purchased.');

        if($user->getMoneys() < $allPrices[$item->getName()])
            throw new UnprocessableEntityHttpException('You don\'t have enough money to buy ' . $item->getName() . '.');

        $cost = $allPrices[$item->getName()];
        $transactionService->spendMoney($user, $cost, 'You bought ' . $item->getName() . ' from the Bookstore.');

        $inventoryService->receiveItem($item, $user, null, $user->getName() . ' bought this from the Book Store.', LocationEnum::HOME, true);

        $em->flush();

        return $responseService->success();
    }
}
