<?php
namespace App\Controller;

use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotEnoughCurrencyException;
use App\Repository\ItemRepository;
use App\Repository\UserQuestRepository;
use App\Service\GrocerService;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\TransactionService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/grocer")
 */
class GrocerController extends AbstractController
{
    /**
     * @Route("", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getInventory(
        GrocerService $grocerService, ResponseService $responseService, UserQuestRepository $userQuestRepository
    )
    {
        /** @var User $user */
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
        UserStatsService $userStatsRepository, UserQuestRepository $userQuestRepository
    )
    {
        $buyTo = $request->request->getInt('location');
        $payWith = strtolower($request->request->getAlpha('payWith', 'moneys'));

        if($buyTo !== LocationEnum::HOME && $buyTo !== LocationEnum::BASEMENT)
            throw new PSPFormValidationException('You must select a location to put the purchased items.');

        if($payWith !== 'moneys' && $payWith !== 'recycling')
            throw new PSPFormValidationException('You must choose whether to pay with moneys or with recycling points.');

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
                    $totalCost += $i[$payWith . 'Cost'] * $quantity;

                    if(!array_key_exists($itemName, $buyingInventory))
                        $buyingInventory[$itemName] = $quantity;
                    else
                        $buyingInventory[$itemName] += $quantity;
                }
            }
        }

        /** @var User $user */
        $user = $this->getUser();
        $now = new \DateTimeImmutable();

        $grocerItemsQuantity = $userQuestRepository->findOrCreate($user, 'Grocer Items Purchased Quantity', 0);
        $grocerItemsDay = $userQuestRepository->findOrCreate($user, 'Grocer Items Purchased Date', $now->format('Y-m-d'));

        if($now->format('Y-m-d') === $grocerItemsDay->getValue())
            $maxCanPurchase = GrocerService::MAX_CAN_PURCHASE_PER_DAY - $grocerItemsQuantity->getValue();
        else
            $maxCanPurchase = GrocerService::MAX_CAN_PURCHASE_PER_DAY;

        if($totalQuantity > $maxCanPurchase)
            throw new PSPInvalidOperationException('Only ' . GrocerService::MAX_CAN_PURCHASE_PER_DAY . ' items per day, please.');

        if(count($buyingInventory) === 0)
            throw new PSPFormValidationException('Did you forget to select something to buy?');

        $existingInventoryCount = $inventoryService->countTotalInventory($user, $buyTo);
        $maxInventory = $buyTo === LocationEnum::BASEMENT ? User::MAX_BASEMENT_INVENTORY : User::MAX_HOUSE_INVENTORY;

        if($existingInventoryCount + $totalQuantity > $maxInventory)
        {
            if($buyTo === LocationEnum::HOME)
                throw new PSPInvalidOperationException('You don\'t have enough space for ' . $totalQuantity . ' more item' . ($totalQuantity === 1 ? '' : 's') . ' in your House.');
            else
                throw new PSPInvalidOperationException('You don\'t have enough space for ' . $totalQuantity . ' more item' . ($totalQuantity === 1 ? '' : 's') . ' in your Basement.');
        }

        if($payWith === 'moneys')
        {
            if($totalCost > $user->getMoneys())
                throw new PSPNotEnoughCurrencyException($totalCost . '~~m~~', $user->getMoneys() . '~~m~~');

            $transactionService->spendMoney($user, $totalCost, 'Purchased ' . $totalQuantity . ' thing' . ($totalQuantity === 1 ? '' : 's') . ' from the Grocer.', true, [ 'Grocer' ]);
        }
        else
        {
            if($totalCost > $user->getRecyclePoints())
                throw new PSPNotEnoughCurrencyException($totalCost . '♺', $user->getRecyclePoints() . '♺');

            $transactionService->spendRecyclingPoints($user, $totalCost, 'Purchased ' . $totalQuantity . ' thing' . ($totalQuantity === 1 ? '' : 's') . ' from the Grocer.', [ 'Grocer' ]);
        }

        foreach($buyingInventory as $itemName=>$quantity)
        {
            for($i = 0; $i < $quantity; $i++)
            {
                $item = ItemRepository::findOneByName($em, $itemName);

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

        $currency = $payWith === 'moneys' ? '~~m~~' : ' recycling points';

        $responseService->addFlashMessage($totalQuantity . ' ' . ($totalQuantity === 1 ? 'item was' : 'items were') . ' purchased for ' . $totalCost . $currency . '. ' . ($totalQuantity === 1 ? 'It' : 'They') . ' can be found in your ' . ($buyTo === LocationEnum::HOME ? 'House' : 'Basement') . '.');

        return $responseService->success([
            'maxPerDay' => GrocerService::MAX_CAN_PURCHASE_PER_DAY,
            'maxRemainingToday' => $maxCanPurchase - $totalQuantity,
        ]);
    }
}