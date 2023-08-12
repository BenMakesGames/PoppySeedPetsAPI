<?php
namespace App\Controller\Pet;

use App\Entity\Pet;
use App\Entity\User;
use App\Enum\SerializationGroupEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPPetNotFoundException;
use App\Repository\PetActivityLogRepository;
use App\Service\Filter\PetActivityLogsFilterService;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/pet")
 */
class LogsController extends AbstractController
{
    /**
     * @Route("/{pet}/logs/calendar/{year}/{month}", methods={"GET"}, requirements={"pet":"\d+", "year":"\d+", "month":"\d+"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function logCalendar(
        ResponseService $responseService, PetActivityLogRepository $petActivityLogRepository,

        // route arguments:
        Pet $pet, ?int $year = null, ?int $month = null
    )
    {
        if($year === null && $month === null)
        {
            $year = (int)date('Y');
            $month = (int)date('n');
        }

        if($month < 1 || $month > 12)
            throw new PSPFormValidationException('"month" must be between 1 and 12!');

        /** @var User $user */
        $user = $this->getUser();

        if($user->getId() !== $pet->getOwner()->getId())
            throw new PSPPetNotFoundException();

        $results = $petActivityLogRepository->findLogsForPetByDate($pet, $year, $month);

        return $responseService->success([
            'year' => $year,
            'month' => $month,
            'calendar' => $results
        ]);
    }

    /**
     * @Route("/{pet}/logs", methods={"GET"}, requirements={"pet"="\d+"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function logs(
        Pet $pet, ResponseService $responseService, PetActivityLogsFilterService $petActivityLogsFilterService,
        Request $request
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        if($user->getId() !== $pet->getOwner()->getId())
            throw new PSPPetNotFoundException();

        $petActivityLogsFilterService->addRequiredFilter('pet', $pet->getId());

        return $responseService->success(
            $petActivityLogsFilterService->getResults($request->query),
            [ SerializationGroupEnum::FILTER_RESULTS, SerializationGroupEnum::PET_ACTIVITY_LOGS ]
        );
    }
}
