<?php
namespace App\Controller\Badge;

use App\Entity\User;
use App\Enum\BadgeEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPInvalidOperationException;
use App\Repository\UserStatsRepository;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/badge")
 */
final class Claim extends AbstractController
{
    /**
     * @Route("/claim", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function claim(
        ResponseService $responseService, Request $request, EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        $badge = trim($request->request->get('badge'));

        if(!$badge || !BadgeEnum::isAValue($badge))
            throw new PSPFormValidationException('Which badge?');

        $progress = BadgeHelpers::getBadgeProgress($badge, $user, $em);

        if(!$progress['done'])
            throw new PSPInvalidOperationException('You are not eligible to claim that badge.');

        return $responseService->success();
    }
}