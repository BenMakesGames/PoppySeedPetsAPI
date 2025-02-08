<?php
declare(strict_types=1);

namespace App\Controller\Achievement;

use App\Entity\User;
use App\Functions\SimpleDb;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/achievement")]
final class Completed extends AbstractController
{
    /**
     * @Route("", methods={"GET"})
     */
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getCompleted(ResponseService $responseService)
    {
        /** @var User $user */
        $user = $this->getUser();

        $badges = SimpleDb::createReadOnlyConnection()
            ->query('SELECT badge, claimed_on AS claimedOn FROM user_badge WHERE user_id = ?', [ $user->getId() ])
            ->getResults();

        return $responseService->success($badges);
    }
}