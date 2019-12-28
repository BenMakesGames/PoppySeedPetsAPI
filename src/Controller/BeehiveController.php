<?php
namespace App\Controller;

use App\Enum\LocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Functions\ArrayFunctions;
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
     * @Route("/feed", methods={"POST"})
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

    /**
     * @Route("/harvest", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function harvest(
        ResponseService $responseService, EntityManagerInterface $em, BeehiveService $beehiveService,
        InventoryService $inventoryService
    )
    {
        $user = $this->getUser();

        if(!$user->getUnlockedBeehive() || !$user->getBeehive())
            throw new AccessDeniedHttpException('You haven\'t got a Beehive, yet!');

        $beehive = $user->getBeehive();

        if($beehive->getRoyalJellyPercent() >= 1)
        {
            $beehive->setRoyalJellyProgress(0);

            $inventoryService->receiveItem('Royal Jelly', $user, $user, $user->getName() . ' took this from their Beehive.', LocationEnum::HOME);
        }

        if($beehive->getHoneycombPercent() >= 1)
        {
            $beehive->setHoneycombProgress(0);

            $inventoryService->receiveItem('Honeycomb', $user, $user, $user->getName() . ' took this from their Beehive.', LocationEnum::HOME);
        }

        if($beehive->getMiscPercent() >= 1)
        {
            $beehive->setMiscProgress(0);

            $item = ArrayFunctions::pick_one([
                'Fluff', 'Talon', 'Yellow Dye', 'Crooked Stick', 'Glue', 'Sugar', 'Antenna'
            ]);

            $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' took this from their Beehive.', LocationEnum::HOME);
        }

        $user->getBeehive()->setInteractionPower();

        $em->flush();

        return $responseService->success($user->getBeehive(), SerializationGroupEnum::MY_BEEHIVE);

    }
}