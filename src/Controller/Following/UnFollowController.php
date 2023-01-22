<?php
namespace App\Controller\Following;

use App\Controller\PoppySeedPetsController;
use App\Entity\User;
use App\Repository\UserFollowingRepository;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Annotations\DoesNotRequireHouseHours;

/**
 * @Route("/following")
 */
class UnFollowController extends PoppySeedPetsController
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
            throw new NotFoundHttpException('You\'re not following that person...');

        $em->remove($followingRecord);
        $em->flush();

        return $responseService->success();
    }
}
