<?php
namespace App\Controller\Account;

use App\Entity\User;
use App\Enum\SerializationGroupEnum;
use App\Repository\UserStyleRepository;
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
        ResponseService $responseService, UserStyleRepository $userStyleRepository
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        return $responseService->success(
            [ 'currentTheme' => $userStyleRepository->findCurrent($user) ],
            [ SerializationGroupEnum::MY_STYLE ]
        );
    }
}
