<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Pet;
use App\Entity\User;
use App\Enum\ParkEventTypeEnum;
use App\Enum\SerializationGroupEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPPetNotFoundException;
use App\Service\Filter\ParkEventHistoryFilterService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/park")]
class ParkController extends AbstractController
{
    #[Route("/signUpPet/{pet}", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function changePetParkEventType(Pet $pet, Request $request, EntityManagerInterface $em, ResponseService $responseService)
    {
        $parkEventType = trim($request->request->getString('parkEventType'));

        if($parkEventType === '') $parkEventType = null;

        if($parkEventType !== null && !ParkEventTypeEnum::isAValue($parkEventType))
            throw new PSPFormValidationException('"' . $parkEventType . '" is not a valid park event type!');

        /** @var User $user */
        $user = $this->getUser();

        if($pet->getOwner()->getId() !== $user->getId())
            throw new PSPPetNotFoundException();

        $pet->setParkEventType($parkEventType);

        $em->flush();

        return $responseService->success();
    }

    #[Route("/history", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getEventHistory(
        Request $request, ResponseService $responseService, ParkEventHistoryFilterService $parkEventHistoryFilterService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        $parkEventHistoryFilterService->setUser($user);

        return $responseService->success(
            $parkEventHistoryFilterService->getResults($request->query),
            [ SerializationGroupEnum::FILTER_RESULTS, SerializationGroupEnum::PARK_EVENT ]
        );
    }
}