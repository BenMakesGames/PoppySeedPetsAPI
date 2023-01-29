<?php
namespace App\Controller\Beehive;

use App\Entity\User;
use App\Enum\SerializationGroupEnum;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/beehive")
 */
class GetController extends AbstractController
{
    /**
     * @Route("", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getBeehive(ResponseService $responseService, EntityManagerInterface $em)
    {
        /** @var User $user */
        $user = $this->getUser();

        if(!$user->getUnlockedBeehive() || !$user->getBeehive())
            throw new AccessDeniedHttpException('You haven\'t got a Beehive, yet!');

        $user->getBeehive()->setInteractionPower();

        $em->flush();

        return $responseService->success($user->getBeehive(), [ SerializationGroupEnum::MY_BEEHIVE, SerializationGroupEnum::HELPER_PET ]);
    }
}
