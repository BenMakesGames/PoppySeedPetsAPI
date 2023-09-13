<?php
namespace App\Controller\Account;

use App\Entity\User;
use App\Enum\SerializationGroupEnum;
use App\Repository\UserStyleRepository;
use App\Service\PerformanceProfiler;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Annotations\DoesNotRequireHouseHours;

/**
 * @Route("/account")
 */
class GetAccountController extends AbstractController
{
    /**
     * @DoesNotRequireHouseHours()
     * @Route("", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getAccount(
        ResponseService $responseService, UserStyleRepository $userStyleRepository,
        PerformanceProfiler $performanceProfiler
    )
    {
        $time = microtime(true);

        /** @var User $user */
        $user = $this->getUser();

        $response = $responseService->success(
            [ 'currentTheme' => $userStyleRepository->findCurrent($user) ],
            [ SerializationGroupEnum::MY_STYLE ]
        );

        //$performanceProfiler->logExecutionTime(__CLASS__, __FUNCTION__, microtime(true) - $time);

        return $response;
    }
}
