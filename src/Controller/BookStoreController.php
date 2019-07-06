<?php
namespace App\Controller;
use App\Entity\Item;
use App\Repository\ItemRepository;
use App\Service\BookStoreService;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

// allows player to buy books; inventory grows based on various criteria

/**
 * @Route("/bookStore")
 */
class BookStoreController extends PsyPetsController
{
    /**
     * @Route("", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getAvailableBooks(
        BookStoreService $bookStoreService, ItemRepository $itemRepository
    )
    {
        $user = $this->getUser();

        $bookPrices = $bookStoreService->getAvailableInventory($user);

        $bookItems = $itemRepository->findBy([ 'name' => array_keys($bookPrices) ], [ 'name' => 'ASC' ]);

        $books = [];

        foreach($bookItems as $bookItem)
        {
            $books[] = [
                'book' => $bookItem,
                'price' => $bookPrices[$bookItem->getName()]
            ];
        }
    }

    /**
     * @Route("/buy/{book}", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function buyBook(
        Item $book, BookStoreService $bookStoreService, InventoryService $inventoryService, EntityManagerInterface $em,
        ResponseService $responseService
    )
    {
        $user = $this->getUser();

        $bookPrices = $bookStoreService->getAvailableInventory($user);

        if(!array_key_exists($book->getName(), $bookPrices))
            throw new UnprocessableEntityHttpException('That item cannot be purchased.');

        if($user->getMoneys() < $bookPrices[$book->getName()])
            throw new UnprocessableEntityHttpException('You don\'t have enough money to buy ' . $book->getName() . '.');

        $user->increaseMoneys(-$bookPrices[$book->getName()]);

        $inventoryService->receiveItem($book, $user, null, $user->getName() . ' bought this from the Book Store.');

        $em->flush();

        $responseService->success();
    }
}