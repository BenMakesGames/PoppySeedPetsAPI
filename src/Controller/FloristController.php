<?php
namespace App\Controller;

use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPNotEnoughCurrencyException;
use App\Exceptions\PSPNotUnlockedException;
use App\Functions\ArrayFunctions;
use App\Service\FloristService;
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
 * @Route("/florist")
 */
class FloristController extends AbstractController
{
    /**
     * @Route("", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getInventory(FloristService $floristService, ResponseService $responseService)
    {
        /** @var User $user */
        $user = $this->getUser();

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Florist))
            throw new PSPNotUnlockedException('Florist');

        return $responseService->success($floristService->getInventory($user));
    }

    /**
     * @Route("/buy", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function buyFlowerbomb(
        Request $request, FloristService $floristService,
        InventoryService $inventoryService, ResponseService $responseService, UserStatsService $userStatsRepository,
        EntityManagerInterface $em, TransactionService $transactionService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Florist))
            throw new PSPNotUnlockedException('Florist');

        $offers = $floristService->getInventory($user);
        $userPickName = $request->request->get('item');

        $userPick = ArrayFunctions::find_one($offers, fn($o) => $o['item']['name'] === $userPickName);

        if(!$userPick)
            throw new PSPFormValidationException('That item is not available... (maybe reload the page and try again??)');

        if($user->getMoneys() < $userPick['cost'])
            throw new PSPNotEnoughCurrencyException($userPick['cost'] . '~~m~~', $user->getMoneys() . '~~m~~');

        $transactionService->spendMoney($user, $userPick['cost'], 'Purchased a ' . $userPick['item']['name'] . ' at The Florist.');

        $inventoryService->receiveItem($userPick['item']['name'], $user, $user, $user->getName() . ' bought this at The Florist\'s.', LocationEnum::HOME, true);

        $statName = $userPick['item']['name'] . 's Purchased';

        $stat = $userStatsRepository->incrementStat($user, $statName);

        if($userPick['item']['name'] === 'Flowerbomb' && $stat->getValue() === 1)
            $inventoryService->receiveItem('Book of Flowers', $user, $user, 'This was delivered to you from The Florist\'s.', LocationEnum::HOME, true);

        $em->flush();

        return $responseService->success();
    }
}
