<?php
namespace App\Controller;

use App\Entity\User;
use App\Enum\SerializationGroupEnum;
use App\Repository\PetActivityLogTagRepository;
use App\Service\Filter\PetActivityLogsFilterService;
use App\Service\ResponseService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/petActivityLogs")
 */
class PetActivityLogsController extends AbstractController
{
    /**
     * @Route("", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function history(
        Request $request, ResponseService $responseService, PetActivityLogsFilterService $petActivityLogsFilterService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        $petActivityLogsFilterService->addRequiredFilter('user', $user->getId());

        $logs = $petActivityLogsFilterService->getResults($request->query);

        return $responseService->success($logs, [ SerializationGroupEnum::FILTER_RESULTS, SerializationGroupEnum::PET_ACTIVITY_LOGS_AND_PUBLIC_PET ]);
    }

    /**
     * @Route("/getAllTags", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getAllTags(ResponseService $responseService, PetActivityLogTagRepository $petActivityLogTagRepository)
    {
        $tags = $petActivityLogTagRepository->findAll();

        return $responseService->success($tags, [ SerializationGroupEnum::PET_ACTIVITY_LOGS ]);
    }
}
