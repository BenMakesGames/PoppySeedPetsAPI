<?php
declare(strict_types=1);

namespace App\Controller;

use App\Attributes\DoesNotRequireHouseHours;
use App\Entity\PetActivityLogTag;
use App\Entity\User;
use App\Enum\SerializationGroupEnum;
use App\Service\Filter\PetActivityLogsFilterService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/petActivityLogs")]
class PetActivityLogsController extends AbstractController
{
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    #[Route("", methods: ["GET"])]
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

    #[DoesNotRequireHouseHours]
    #[Route("/getAllTags", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getAllTags(ResponseService $responseService, EntityManagerInterface $em)
    {
        $tags = $em->getRepository(PetActivityLogTag::class)->findAll();

        return $responseService->success($tags, [ SerializationGroupEnum::PET_ACTIVITY_LOGS ]);
    }
}
