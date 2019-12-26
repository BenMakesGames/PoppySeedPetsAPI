<?php
namespace App\Controller;

use App\Enum\SerializationGroupEnum;
use App\Repository\InventoryRepository;
use App\Repository\UserQuestRepository;
use App\Service\BeehiveService;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/beehive")
 */
class BeehiveController extends PoppySeedPetsController
{
    /**
     * @Route("", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getBeehive(ResponseService $responseService, EntityManagerInterface $em)
    {
        $user = $this->getUser();

        if(!$user->getUnlockedBeehive() || !$user->getBeehive())
            throw new AccessDeniedHttpException('You haven\'t got a Beehive, yet!');

        $user->getBeehive()->setInteractionPower();

        $em->flush();

        return $responseService->success($user->getBeehive(), SerializationGroupEnum::MY_BEEHIVE);
    }

    /**
     * @Route("/feed", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function feedItem(ResponseService $responseService, EntityManagerInterface $em, BeehiveService $beehiveService)
    {
        $user = $this->getUser();

        if(!$user->getUnlockedBeehive() || !$user->getBeehive())
            throw new AccessDeniedHttpException('You haven\'t got a Beehive, yet!');

        if($user->getBeehive()->getFlowerPower() > 0)
            throw new UnprocessableEntityHttpException('The colony is still working on the last item you gave them.');

        // @TODO consume item; increase flower power; pick new request

        $beehiveService->fedRequestedItem($user->getBeehive());
        $user->getBeehive()->setInteractionPower();

        $em->flush();

        return $responseService->success($user->getBeehive(), SerializationGroupEnum::MY_BEEHIVE);
    }

    /**
     * @Route("/reRoll", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function reRollRequest(ResponseService $responseService, EntityManagerInterface $em, BeehiveService $beehiveService)
    {
        $user = $this->getUser();

        if(!$user->getUnlockedBeehive() || !$user->getBeehive())
            throw new AccessDeniedHttpException('You haven\'t got a Beehive, yet!');

        // @TODO consume die; reroll

        $beehiveService->reRollRequest($user->getBeehive());

        $user->getBeehive()->setInteractionPower();

        $em->flush();

        return $responseService->success($user->getBeehive(), SerializationGroupEnum::MY_BEEHIVE);
    }
}