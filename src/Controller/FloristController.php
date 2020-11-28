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
     * @Route("/buyFlowerbomb", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function buyFlowerbomb(
        InventoryService $inventoryService, ResponseService $responseService, UserStatsRepository $userStatsRepository,
        EntityManagerInterface $em, TransactionService $transactionService
    )
    {
        $user = $this->getUser();

        if($user->getUnlockedFlorist() === null)
            throw new AccessDeniedHttpException('You have not unlocked this feature yet.');

        if($user->getMoneys() < 150)
            throw new UnprocessableEntityHttpException('"It seems you don\'t have quite enough moneys."');

        $transactionService->spendMoney($user, 150, 'Purchased a Flowerbomb at The Florist.');

        $inventoryService->receiveItem('Flowerbomb', $user, $user, $user->getName() . ' bought this at The Florist\'s.', LocationEnum::HOME, true);

        $stat = $userStatsRepository->incrementStat($user, UserStatEnum::FLOWERBOMBS_PURCHASED);

        if($stat->getValue() === 1)
            $inventoryService->receiveItem('Book of Flowers', $user, $user, 'This was delivered to you from The Florist\'s.', LocationEnum::HOME, true);

        $em->flush();

        return $responseService->success();
    }

}
