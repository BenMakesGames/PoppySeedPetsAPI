<?php
namespace App\Controller;

use App\Entity\Item;
use App\Enum\SerializationGroupEnum;
use App\Enum\UserStatEnum;
use App\Repository\ItemRepository;
use App\Repository\UserStatsRepository;
use App\Service\BookstoreService;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

// allows player to buy books; inventory grows based on various criteria

/**
 * @Route("/bookstore")
 */
class BookstoreController extends PsyPetsController
{
    /**
     * @Route("", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getAvailableBooks(
        BookstoreService $bookstoreService, ItemRepository $itemRepository, ResponseService $responseService
    )
    {
        $user = $this->getUser();

        if($user->getUnlockedBookstore() === null)
            throw new AccessDeniedHttpException('You have not unlocked this feature yet.');

        $bookPrices = $bookstoreService->getAvailableInventory($user);

        $bookItems = $itemRepository->findBy([ 'name' => array_keys($bookPrices) ], [ 'name' => 'ASC' ]);

        $books = [];

        foreach($bookItems as $bookItem)
        {
            $books[] = [
                'item' => $bookItem,
                'price' => $bookPrices[$bookItem->getName()]
            ];
        }

        return $responseService->success($books, [ SerializationGroupEnum::MARKET_ITEM ]);
    }

    /**
     * @Route("/{book}/buy", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function buyBook(
        Item $book, BookstoreService $bookstoreService, InventoryService $inventoryService, EntityManagerInterface $em,
        ResponseService $responseService, UserStatsRepository $userStatsRepository
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
        $user->increaseMoneys(-$cost);
        $userStatsRepository->incrementStat($user, UserStatEnum::TOTAL_MONEYS_SPENT, $cost);

        $inventoryService->receiveItem($book, $user, null, $user->getName() . ' bought this from the Book Store.');

        $em->flush();

        return $responseService->success();
    }
}