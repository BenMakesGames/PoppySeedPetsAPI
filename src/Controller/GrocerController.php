<?php
namespace App\Controller;

use App\Entity\Inventory;
use App\Enum\LocationEnum;
use App\Repository\ItemRepository;
use App\Repository\UserQuestRepository;
use App\Repository\UserStatsRepository;
use App\Service\GrocerService;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\TransactionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/grocer")
 */
class GrocerController extends PoppySeedPetsController
{
    /**
     * @Route("", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getInventory(
        GrocerService $grocerService, ResponseService $responseService, UserQuestRepository $userQuestRepository
    )
    {
        $user = $this->getUser();
        $now = new \DateTimeImmutable();

        $grocerItemsQuantity = $userQuestRepository->findOrCreate($user, 'Grocer Items Purchased Quantity', 0);
        $grocerItemsDay = $userQuestRepository->findOrCreate($user, 'Grocer Items Purchased Date', $now->format('Y-m-d'));

        if($now->format('Y-m-d') === $grocerItemsDay->getValue())
            $maxCanPurchase = GrocerService::MAX_CAN_PURCHASE_PER_DAY - $grocerItemsQuantity->getValue();
        else
            $maxCanPurchase = GrocerService::MAX_CAN_PURCHASE_PER_DAY;

        return $responseService->success([
            'inventory' => $grocerService->getInventory(),
            'maxPerDay' => GrocerService::MAX_CAN_PURCHASE_PER_DAY,
            'maxRemainingToday' => $maxCanPurchase,
        ]);
    }

    /**
     * @Route("/buy", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function buy(
        Request $request, ResponseService $responseService, GrocerService $grocerService,
        TransactionService $transactionService, InventoryService $inventoryService, EntityManagerInterface $em,
        ItemRepository $itemRepository, UserStatsRepository $userStatsRepository,
        UserQuestRepository $userQuestRepository
    )
    {
        $buyTo = $request->request->getInt('location');

        if($buyTo !== LocationEnum::HOME && $buyTo !== LocationEnum::BASEMENT)
            throw new UnprocessableEntityHttpException('You must select a location to put the purchased items.');

        $inventory = $grocerService->getInventory();

        $buyingInventory = [];
        $totalQuantity = 0;
        $totalCost = 0;

        foreach($inventory as $i)
        {
            $itemName = $i['item']['name'];

            if($request->request->has($itemName))
            {
                $quantity = $request->request->getInt($itemName);

                if($quantity > 0)
                {
                    $totalQuantity += $quantity;
                    $totalCost += $i['cost'] * $quantity;

                    if(!array_key_exists($itemName, $buyingInventory))
                        $buyingInventory[$itemName] = $quantity;
                    else
                        $buyingInventory[$itemName] += $quantity;
                }
            }
        }

        $user = $this->getUser();
        $now = new \DateTimeImmutable();

        $grocerItemsQuantity = $userQuestRepository->findOrCreate($user, 'Grocer Items Purchased Quantity', 0);
        $grocerItemsDay = $userQuestRepository->findOrCreate($user, 'Grocer Items Purchased Date', $now->format('Y-m-d'));

        if($now->format('Y-m-d') === $grocerItemsDay->getValue())
            $maxCanPurchase = GrocerService::MAX_CAN_PURCHASE_PER_DAY - $grocerItemsQuantity->getValue();
        else
            $maxCanPurchase = GrocerService::MAX_CAN_PURCHASE_PER_DAY;

        if($totalQuantity > $maxCanPurchase)
            throw new UnprocessableEntityHttpException('Only ' . GrocerService::MAX_CAN_PURCHASE_PER_DAY . ' items per day, please.');

        if(count($buyingInventory) === 0)
            throw new UnprocessableEntityHttpException('Did you forget to select something to buy?');

        $existingInventoryCount = $inventoryService->countTotalInventory($user, $buyTo);
        $maxInventory = $buyTo === LocationEnum::BASEMENT ? 10000 : 100;

        if($existingInventoryCount + $totalQuantity > $maxInventory)
        {
            if($buyTo === LocationEnum::HOME)
                throw new UnprocessableEntityHttpException('You don\'t have enough space for ' . $totalQuantity . ' more item' . ($totalQuantity === 1 ? '' : 's') . ' in your House.');
            else
                throw new UnprocessableEntityHttpException('You don\'t have enough space for ' . $totalQuantity . ' more item' . ($totalQuantity === 1 ? '' : 's') . ' in your Basement.');
        }

        if($totalCost > $user->getMoneys())
            throw new UnprocessableEntityHttpException('That would cost a total of ' . $totalCost . '~~m~~, but you only have ' . $user->getMoneys() . '~~m~~...');

        $transactionService->spendMoney($user, $totalCost, 'Purchased ' . $totalQuantity . ' thing' . ($totalQuantity === 1 ? '' : 's') . ' from the Grocer.');

        foreach($buyingInventory as $itemName=>$quantity)
        {
            for($i = 0; $i < $quantity; $i++)
            {
                $item = $itemRepository->findOneByName($itemName);

                $newInventory = (new Inventory())
                    ->setItem($item)
                    ->setLocation($buyTo)
                    ->setLockedToOwner(true)
                    ->setOwner($user)
                    ->addComment($user->getName() . ' bought this from the Grocery Store.')
                ;

                $em->persist($newInventory);
            }
        }

        $userStatsRepository->incrementStat($user, 'Items Purchased from Grocer', $totalQuantity);

        if($now->format('Y-m-d') === $grocerItemsDay->getValue())
            $grocerItemsQuantity->setValue($grocerItemsQuantity->getValue() + $totalQuantity);
        else
        {
            $grocerItemsDay->setValue($now->format('Y-m-d'));
            $grocerItemsQuantity->setValue($totalQuantity);
        }

        $em->flush();

        $responseService->addFlashMessage($totalQuantity . ' ' . ($totalQuantity === 1 ? 'item was' : 'items were') . ' purchased for ' . $totalCost . '~~m~~. ' . ($totalQuantity === 1 ? 'It' : 'They') . ' can be found in your ' . ($buyTo === LocationEnum::HOME ? 'House' : 'Basement') . '.');

        return $responseService->success([
            'maxPerDay' => GrocerService::MAX_CAN_PURCHASE_PER_DAY,
            'maxRemainingToday' => $maxCanPurchase - $totalQuantity,
        ]);
    }
}