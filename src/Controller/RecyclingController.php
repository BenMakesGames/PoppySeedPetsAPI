<?php
namespace App\Controller;

use App\Enum\LocationEnum;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/recycling")
 */
class RecyclingController extends PoppySeedPetsController
{
    /**
     * @Route("/gamble", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function harvestPlant(
        ResponseService $responseService, EntityManagerInterface $em, InventoryService $inventoryService,
        Request $request
    )
    {
        $user = $this->getUser();

        $bet = $request->request->getInt('bet');

        if($user->getRecyclePoints() < 100)
            throw new AccessDeniedHttpException('You don\'t have enough !');

        $r1 = mt_rand(1, 6);
        $r2 = mt_rand(1, 6);

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

        $user->increaseRecyclePoints($points - 100);

        foreach($items as $itemName)
            $inventoryService->receiveItem($itemName, $user, $user, $user->getName() . ' got this from a magic Satyr dice roll.', LocationEnum::HOME);

        $em->flush();

        return $responseService->success([
            'dice' => [ $r1, $r2 ],
            'getDouble' => $getDouble,
            'points' => $points,
            'items' => $items
        ]);
    }
}
