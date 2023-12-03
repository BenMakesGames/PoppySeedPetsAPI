<?php
namespace App\Controller;

use App\Entity\User;
use App\Enum\LocationEnum;
use App\Exceptions\PSPNotEnoughCurrencyException;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\TransactionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/recycling")]
class RecyclingController extends AbstractController
{
    #[Route("/gamble", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function gamble(
        ResponseService $responseService, EntityManagerInterface $em, InventoryService $inventoryService,
        Request $request, IRandom $squirrel3, TransactionService $transactionService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        $bet = $request->request->getInt('bet');

        if($user->getRecyclePoints() < 100)
            throw new PSPNotEnoughCurrencyException('100♺', $user->getRecyclePoints() . '♺');

        $r1 = $squirrel3->rngNextInt(1, 6);
        $r2 = $squirrel3->rngNextInt(1, 6);

        $total = $r1 + $r2;

        $items = [];
        $points = 0;

        if($total === 12)
        {
            $items = [ 'Shiny Baabble', 'Shiny Baabble' ];
        }
        else if($total === 11)
        {
            $items = [ 'Shiny Baabble' ];
        }
        else if($total === 10)
        {
            $items = [ 'Gold Baabble' ];
        }
        else if($total === 9)
        {
            $items = [ 'White Baabble' ];
        }
        else if($total === 8)
        {
            $items = [ 'Black Baabble' ];
            $points = 75;
        }
        else if($total >= 5 && $total <= 7)
        {
            $items = [ 'Black Baabble' ];
        }
        else if($total === 3 || $total === 4)
        {
            $items = [ 'Creamy Milk', 'Paper Bag' ];
        }
        else if($total === 2)
        {
            $items = [ 'Creamy Milk' ];
        }

        $getDouble =
            ($total > 8 && $bet > 0) ||
            ($total === 8 && $bet === 0) ||
            ($total < 8 && $bet < 0)
        ;

        if($getDouble)
        {
            $points *= 2;
            $items = array_merge($items, $items);
        }

        sort($items);

        $transactionService->spendRecyclingPoints($user, 100, 'Spent at a game of Satyr Dice.', [ 'Satyr Dice' ]);

        if($points > 0)
            $transactionService->getRecyclingPoints($user, $points, 'Earned at a game of Satyr Dice.', [ 'Satyr Dice' ]);

        foreach($items as $itemName)
            $inventoryService->receiveItem($itemName, $user, $user, $user->getName() . ' got this from a game of Satyr Dice.', LocationEnum::HOME);

        $em->flush();

        return $responseService->success([
            'dice' => [ $r1, $r2 ],
            'getDouble' => $getDouble,
            'points' => $points,
            'items' => $items
        ]);
    }
}
