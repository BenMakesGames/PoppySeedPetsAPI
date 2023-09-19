<?php
namespace App\Controller\Account;

use App\Entity\User;
use App\Enum\SerializationGroupEnum;
use App\Functions\UserStyleFunctions;
use App\Service\PerformanceProfiler;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
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
        ResponseService $responseService, EntityManagerInterface $em,
        PerformanceProfiler $performanceProfiler
    )
    {
        $time = microtime(true);

        /** @var User $user */
        $user = $this->getUser();

        $currentTheme = UserStyleFunctions::findCurrent($em, $user->getId());

        $response = $responseService->success(
            [ 'currentTheme' => $currentTheme ],
            [ SerializationGroupEnum::MY_STYLE ]
        );

        $performanceProfiler->logExecutionTime(__METHOD__, microtime(true) - $time);

        return $response;
    }
}
