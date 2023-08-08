<?php
namespace App\Controller\Badge;

use App\Entity\User;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/badge")
 */
final class Claim extends AbstractController
{
    /**
     * @Route("/claim", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function claim(ResponseService $responseService)
    {
        /** @var User $user */
        $user = $this->getUser();
    }
}