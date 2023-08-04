<?php
namespace App\Controller\Following;

use App\Entity\User;
use App\Entity\UserFollowing;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotFoundException;
use App\Repository\UserFollowingRepository;
use App\Repository\UserRepository;
use App\Repository\UserUnlockedFeatureRepository;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Annotations\DoesNotRequireHouseHours;

/**
 * @Route("/following")
 */
class FollowController extends AbstractController
{
    /**
     * @DoesNotRequireHouseHours()
     * @Route("", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function add(
        Request $request, UserRepository $userRepository, UserFollowingRepository $userFollowingRepository,
        ResponseService $responseService, EntityManagerInterface $em,
        UserUnlockedFeatureRepository $userUnlockedFeatureRepository
    )
    {
        /** @var User $user */
        $user = $this->getUser();
        $followingId = $request->request->getInt('following');
        $note = $request->request->get('note', null);

        if($followingId === $user->getId())
            throw new PSPInvalidOperationException('You can\'t follow yourself! That\'s so RANDOM! You\'re so RANDOM!');

        $following = $userRepository->find($followingId);

        if(!$following)
            throw new PSPNotFoundException('Could not find a person with that number.');

        $alreadyFollowing = $userFollowingRepository->findOneBy([
            'user' => $user,
            'following' => $following
        ]);

        if($alreadyFollowing)
            throw new PSPInvalidOperationException('You\'re already following that person.');

        $newFriend = (new UserFollowing())
            ->setUser($user)
            ->setFollowing($following)
            ->setNote($note)
        ;

        $em->persist($newFriend);

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Florist))
            $userUnlockedFeatureRepository->create($user, UnlockableFeatureEnum::Florist);

        $em->flush();

        return $responseService->success();
    }
}
