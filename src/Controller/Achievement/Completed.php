<?php
namespace App\Controller\Achievement;

use App\Entity\User;
use App\Entity\UserBadge;
use App\Functions\SimpleDb;
use App\Repository\UserBadgeRepository;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/achievement")
 */
final class Completed extends AbstractController
{
    /**
     * @Route("", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getCompleted(ResponseService $responseService, UserBadgeRepository $userBadgeRepository)
    {
        /** @var User $user */
        $user = $this->getUser();

        $badges = SimpleDb::createReadOnlyConnection()
            ->query('SELECT badge, claimed_on AS claimedOn FROM user_badge WHERE user_id = ?', [ $user->getId() ])
            ->getResults();

        return $responseService->success($badges);
    }
}