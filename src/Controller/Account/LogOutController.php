<?php
declare(strict_types=1);

namespace App\Controller\Account;

use App\Attributes\DoesNotRequireHouseHours;
use App\Service\ResponseService;
use App\Service\SessionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/account")]
class LogOutController extends AbstractController
{
    #[DoesNotRequireHouseHours]
    #[Route("/logOut", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function logOut(EntityManagerInterface $em, ResponseService $responseService, SessionService $sessionService)
    {
        $sessionService->logOut();

        $em->flush();

        return $responseService->success();
    }
}
