<?php
namespace App\Controller;

use App\Entity\Pet;
use App\Entity\PetSkills;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Enum\UserStatEnum;
use App\Functions\ArrayFunctions;
use App\Functions\GrammarFunctions;
use App\Repository\InventoryRepository;
use App\Repository\PetSpeciesRepository;
use App\Repository\UserQuestRepository;
use App\Repository\UserRepository;
use App\Repository\UserStatsRepository;
use App\Service\Filter\UserFilterService;
use App\Service\FloristService;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\SessionService;
use App\Service\TransactionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/florist")
 */
class FloristController extends PoppySeedPetsController
{
    /**
     * @Route("", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getInventory(FloristService $floristService, ResponseService $responseService)
    {
        $user = $this->getUser();

        if($user->getUnlockedFlorist() === null)
            throw new AccessDeniedHttpException('You have not unlocked this feature yet.');

        return $responseService->success($floristService->getInventory($user));
    }

    /**
     * @Route("/buy", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function buyFlowerbomb(
        Request $request, FloristService $floristService,
        InventoryService $inventoryService, ResponseService $responseService, UserStatsRepository $userStatsRepository,
        EntityManagerInterface $em, TransactionService $transactionService
    )
    {
        $user = $this->getUser();

        if($user->getUnlockedFlorist() === null)
            throw new AccessDeniedHttpException('You have not unlocked this feature yet.');

        $offers = $floristService->getInventory($user);
        $userPickName = $request->request->get('item');

        $userPick = ArrayFunctions::find_one($offers, fn($o) => $o['item']['name'] === $userPickName);

        if(!$userPick)
            throw new UnprocessableEntityHttpException('That item is not available... (maybe reload the page and try again??)');

        if($user->getMoneys() < $userPick['cost'])
            throw new UnprocessableEntityHttpException('"It seems you don\'t have quite enough moneys."');

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
