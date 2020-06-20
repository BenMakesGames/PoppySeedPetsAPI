<?php
namespace App\Controller;

use App\Entity\Item;
use App\Entity\PetActivityLog;
use App\Enum\LocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Repository\ItemRepository;
use App\Repository\UserQuestRepository;
use App\Service\BookstoreService;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\TransactionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

// allows player to buy books; inventory grows based on various criteria

/**
 * @Route("/bookstore")
 */
class BookstoreController extends PoppySeedPetsController
{
    /**
     * @Route("", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getAvailableBooks(
        BookstoreService $bookstoreService, ResponseService $responseService
    )
    {
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
        $user = $this->getUser();

        if($user->getUnlockedBookstore() === null)
            throw new AccessDeniedHttpException('You have not unlocked this feature yet.');

        $bookstoreService->advanceBookstoreQuest($user, $item);

        $em->flush();

        $data = $bookstoreService->getResponseData($user);

        $responseService->addFlashMessage((new PetActivityLog())->setEntry('Thanks! Renaming Scrolls now cost ' . $bookstoreService->getRenamingScrollCost($user) . '~~m~~!'));

        return $responseService->success($data, [ SerializationGroupEnum::MARKET_ITEM ]);
    }

    /**
     * @Route("/{book}/buy", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function buyBook(
        Item $book, BookstoreService $bookstoreService, InventoryService $inventoryService, EntityManagerInterface $em,
        ResponseService $responseService, TransactionService $transactionService
    )
    {
        $user = $this->getUser();

        if($user->getUnlockedBookstore() === null)
            throw new AccessDeniedHttpException('You have not unlocked this feature yet.');

        $bookPrices = $bookstoreService->getAvailableInventory($user);

        if(!array_key_exists($book->getName(), $bookPrices))
            throw new UnprocessableEntityHttpException('That item cannot be purchased.');

        if($user->getMoneys() < $bookPrices[$book->getName()])
            throw new UnprocessableEntityHttpException('You don\'t have enough money to buy ' . $book->getName() . '.');

        $cost = $bookPrices[$book->getName()];
        $transactionService->spendMoney($user, $cost, 'You bought ' . $book->getName() . ' from the Bookstore.');

        $inventoryService->receiveItem($book, $user, null, $user->getName() . ' bought this from the Book Store.', LocationEnum::HOME);

        $em->flush();

        return $responseService->success();
    }
}
