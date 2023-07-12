<?php
namespace App\Controller\Following;

use App\Entity\User;
use App\Exceptions\PSPNotFoundException;
use App\Repository\UserFollowingRepository;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Annotations\DoesNotRequireHouseHours;

/**
 * @Route("/following")
 */
class UnFollowController extends AbstractController
{
    /**
     * @DoesNotRequireHouseHours()
     * @Route("/{following}", methods={"DELETE"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function handle(
        User $following, ResponseService $responseService, EntityManagerInterface $em,
        UserFollowingRepository $userFollowingRepository
    )
    {
        $user = $this->getUser();

        $followingRecord = $userFollowingRepository->findOneBy([
            'user' => $user,
            'following' => $following,
        ]);

        if(!$followingRecord)
            throw new PSPNotFoundException('You\'re not following that person...');

        $em->remove($followingRecord);
        $em->flush();

        return $responseService->success();
    }
}
