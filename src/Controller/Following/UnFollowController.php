<?php
declare(strict_types=1);

namespace App\Controller\Following;

use App\Entity\User;
use App\Entity\UserFollowing;
use App\Exceptions\PSPNotFoundException;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Annotations\DoesNotRequireHouseHours;

#[Route("/following")]
class UnFollowController extends AbstractController
{
    /**
     * @DoesNotRequireHouseHours()
     * @Route("/{following}", methods={"DELETE"})
     */
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function handle(
        User $following, ResponseService $responseService, EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        $followingRecord = $em->getRepository(UserFollowing::class)->findOneBy([
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
