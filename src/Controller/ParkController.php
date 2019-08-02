<?php
namespace App\Controller;

use App\Entity\Pet;
use App\Enum\ParkEventTypeEnum;
use App\Enum\SerializationGroupEnum;
use App\Service\Filter\ParkEventHistoryFilterService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/park")
 */
class ParkController extends PsyPetsController
{
    /**
     * @Route("/signUpPet/{pet}", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function changePetParkEventType(Pet $pet, Request $request, EntityManagerInterface $em, ResponseService $responseService)
    {
        $parkEventType = trim($request->request->get('parkEventType', ''));

        if($parkEventType === '') $parkEventType = null;

        if($parkEventType !== null && !ParkEventTypeEnum::isAValue($parkEventType))
            throw new UnprocessableEntityHttpException('"' . $parkEventType . '" is not a valid park event type!');

        $user = $this->getUser();

        if($pet->getOwner()->getId() !== $user->getId())
            throw new AccessDeniedHttpException('This is not your pet??? Reload and try again.');

        $pet->setParkEventType($parkEventType);

        $em->flush();

        return $responseService->success();
    }

    /**
     * @Route("/history", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getEventHistory(
        Request $request, ResponseService $responseService, ParkEventHistoryFilterService $parkEventHistoryFilterService
    )
    {
        $parkEventHistoryFilterService->setUser($this->getUser());

        return $responseService->success(
            $parkEventHistoryFilterService->getResults($request->query),
            [ SerializationGroupEnum::FILTER_RESULTS, SerializationGroupEnum::PARK_EVENT ]
        );
    }
}