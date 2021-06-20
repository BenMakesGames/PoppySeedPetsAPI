<?php
namespace App\Controller;

use App\Entity\User;
use App\Entity\UserFollowing;
use App\Enum\SerializationGroupEnum;
use App\Repository\UserFollowingRepository;
use App\Repository\UserRepository;
use App\Service\Filter\UserFilterService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/following")
 */
class FollowingController extends PoppySeedPetsController
{
    /**
     * @DoesNotRequireHouseHours()
     * @Route("", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function add(
        Request $request, UserRepository $userRepository, UserFollowingRepository $userFollowingRepository,
        ResponseService $responseService, EntityManagerInterface $em
    )
    {
        $user = $this->getUser();
        $followingId = $request->request->getInt('following');
        $note = $request->request->get('note', null);

        if($followingId === $user->getId())
            throw new UnprocessableEntityHttpException('I mean... that\'s kind of a given (I hope!?)');

        $following = $userRepository->find($followingId);

        if(!$following)
            throw new NotFoundHttpException('Could not find a person with that number.');

        $alreadyFollowing = $userFollowingRepository->findOneBy([
            'user' => $user,
            'following' => $following
        ]);

        if($alreadyFollowing)
            throw new UnprocessableEntityHttpException('You\'re already following that person.');

        $newFriend = (new UserFollowing())
            ->setUser($user)
            ->setFollowing($following)
            ->setNote($note)
        ;

        $em->persist($newFriend);

        if($user->getUnlockedFlorist() === null)
            $user->setUnlockedFlorist();

        $em->flush();

        return $responseService->success();
    }

    /**
     * @DoesNotRequireHouseHours()
     * @Route("", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function search(
        Request $request, ResponseService $responseService, UserFilterService $userFilterService
    )
    {
        $user = $this->getUser();

        $userFilterService->setUser($user);
        $userFilterService->addDefaultFilter('followedBy', $user->getId());

        return $responseService->success(
            $userFilterService->getResults($request->query),
            [ SerializationGroupEnum::FILTER_RESULTS, SerializationGroupEnum::USER_PUBLIC_PROFILE ]
        );
    }

    /**
     * @DoesNotRequireHouseHours()
     * @Route("/{following}", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function updateNote(
        User $following, Request $request, ResponseService $responseService, EntityManagerInterface $em,
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

        $note = $request->request->get('note', null);

        if($note && \mb_strlen($note) > 255)
            throw new UnprocessableEntityHttpException('Note may not be longer than 255 characters. Sorry :(');

        $followingRecord->setNote($note);

        $em->flush();

        return $responseService->success();
    }

    /**
     * @DoesNotRequireHouseHours()
     * @Route("/{following}", methods={"DELETE"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function removeFollowing(
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
