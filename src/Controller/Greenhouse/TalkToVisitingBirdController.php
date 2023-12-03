<?php
namespace App\Controller\Greenhouse;

use App\Exceptions\PSPNotFoundException;
use App\Exceptions\PSPNotUnlockedException;
use App\Service\GreenhouseService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/greenhouse")]
class TalkToVisitingBirdController extends AbstractController
{
    #[Route("/talkToVisitingBird", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function talkToBird(
        ResponseService $responseService, EntityManagerInterface $em, GreenhouseService $greenhouseService
    )
    {
        $user = $this->getUser();

        if(!$user->getGreenhouse())
            throw new PSPNotUnlockedException('Greenhouse');

        if(!$user->getGreenhouse()->getVisitingBird())
            throw new PSPNotFoundException('Hm... there\'s no bird here. Reload, maybe??');

        $message = $greenhouseService->approachBird($user->getGreenhouse());

        $em->flush();

        $responseService->addFlashMessage($message);

        return $responseService->success();
    }
}
