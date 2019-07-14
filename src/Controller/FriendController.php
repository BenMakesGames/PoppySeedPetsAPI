<?php
namespace App\Controller;

use App\Entity\User;
use App\Entity\UserFriend;
use App\Enum\SerializationGroupEnum;
use App\Repository\UserFriendRepository;
use App\Repository\UserRepository;
use App\Service\Filter\UserFilterService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/friend")
 */
class FriendController extends PsyPetsController
{
    /**
     * @Route("", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function add(
        Request $request, UserRepository $userRepository, UserFriendRepository $userFriendRepository,
        ResponseService $responseService, EntityManagerInterface $em
    )
    {
        $user = $this->getUser();
        $friendId = $request->request->getInt('friend');
        $note = $request->request->get('note', null);

        if($friendId === $user->getId())
            throw new UnprocessableEntityHttpException('I mean... that\'s kind of a given (I hope!?)');

        $friend = $userRepository->find($friendId);

        if(!$friend)
            throw new NotFoundHttpException('Could not find a person with that number.');

        $existingFriend = $userFriendRepository->findOneBy([
            'user' => $user,
            'friend' => $friend
        ]);

        if($existingFriend)
            throw new UnprocessableEntityHttpException('You\'ve already friended that person.');

        $newFriend = (new UserFriend())
            ->setUser($user)
            ->setFriend($friend)
            ->setNote($note)
        ;

        $em->persist($newFriend);

        if($user->getUnlockedFlorist() === null)
            $user->setUnlockedFlorist();

        $em->flush();

        return $responseService->success();
    }

    /**
     * @Route("", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function search(
        Request $request, ResponseService $responseService, UserFilterService $userFilterService
    )
    {
        $user = $this->getUser();

        $userFilterService->setUser($user);
        $userFilterService->addDefaultFilter('friendedBy', $user->getId());

        return $responseService->success(
            $userFilterService->getResults($request->query),
            [ SerializationGroupEnum::FILTER_RESULTS, SerializationGroupEnum::USER_PUBLIC_PROFILE ]
        );
    }

    /**
     * @Route("/{friend}", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function updateFriend(
        User $friend, Request $request, ResponseService $responseService, EntityManagerInterface $em,
        UserFriendRepository $userFriendRepository
    )
    {
        $user = $this->getUser();

        $userFriend = $userFriendRepository->findOneBy([
            'user' => $user,
            'friend' => $friend,
        ]);

        if(!$userFriend)
            throw new NotFoundHttpException('That friend doesn\'t exist...');

        $note = $request->request->get('note', null);

        if($note && strlen($note) > 255)
            throw new UnprocessableEntityHttpException('Note may not be longer than 255 characters. Sorry :(');

        $userFriend->setNote($note);

        $em->flush();

        return $responseService->success();
    }

    /**
     * @Route("/{friend}", methods={"DELETE"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function removeFriend(
        User $friend, ResponseService $responseService, EntityManagerInterface $em, UserFriendRepository $userFriendRepository
    )
    {
        $user = $this->getUser();

        $userFriend = $userFriendRepository->findOneBy([
            'user' => $user,
            'friend' => $friend,
        ]);

        if(!$userFriend)
            throw new NotFoundHttpException('That friend doesn\'t exist...');

        $em->remove($userFriend);
        $em->flush();

        return $responseService->success();
    }
}