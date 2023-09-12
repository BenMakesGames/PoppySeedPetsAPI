<?php
namespace App\Controller;

use App\Entity\User;
use App\Entity\UserActivityLogTag;
use App\Enum\SerializationGroupEnum;
use App\Service\Filter\UserActivityLogsFilterService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/userActivityLogs")
 */
class UserActivityLogsController extends AbstractController
{
    /**
     * @Route("", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function history(
        Request $request, ResponseService $responseService, UserActivityLogsFilterService $userActivityLogsFilterService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        $userActivityLogsFilterService->addRequiredFilter('user', $user->getId());

        $logs = $userActivityLogsFilterService->getResults($request->query);

        return $responseService->success($logs, [ SerializationGroupEnum::FILTER_RESULTS, SerializationGroupEnum::USER_ACTIVITY_LOGS ]);
    }

    /**
     * @Route("/getAllTags", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getAllTags(ResponseService $responseService, EntityManagerInterface $em)
    {
        $tags = $em->getRepository(UserActivityLogTag::class)->findAll();

        return $responseService->success($tags, [ SerializationGroupEnum::USER_ACTIVITY_LOGS ]);
    }
}
