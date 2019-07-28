<?php
namespace App\Controller;

use App\Entity\ParkEvent;
use App\Entity\Pet;
use App\Enum\SerializationGroupEnum;
use App\Repository\ParkEventRepository;
use App\Service\Filter\ParkEventFilterService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/park")
 */
class ParkController extends PsyPetsController
{
    /**
     * @Route("", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getEvents(
        Request $request, ResponseService $responseService, ParkEventFilterService $parkEventFilterService
    )
    {
        $parkEventFilterService->addRequiredFilter('isOpen', true);

        return $responseService->success(
            $parkEventFilterService->getResults($request->query),
            [ SerializationGroupEnum::FILTER_RESULTS, SerializationGroupEnum::PARK_EVENT ]
        );
    }

    /**
     * @Route("/{event}/register/{pet}", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function registerPet(
        ParkEvent $event, Pet $pet, EntityManagerInterface $em, ResponseService $responseService
    )
    {
        $user = $this->getUser();

        if($pet->getOwner()->getId() !== $user->getId())
            throw new UnprocessableEntityHttpException('That pet does not belong to you.');

        if($pet->getLastParkEventJoinedOn()->format('Ymd') === (new \DateTimeImmutable())->format('Ymd'))
            throw new UnprocessableEntityHttpException('That pet already signed up for a park event today.');

        if($event->getRanOn() !== null)
            throw new UnprocessableEntityHttpException('This event has already completed.');

        if($event->getParticipants()->count() >= $event->getSeats())
            throw new UnprocessableEntityHttpException('This event is already full.');

        $event->addParticipant($pet);
        $pet->setLastParkEventJoinedOn();

        $em->flush();

        return $responseService->success();
    }
}