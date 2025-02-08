<?php
declare(strict_types=1);

namespace App\Controller\Following;

use App\Entity\User;
use App\Entity\UserFollowing;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotFoundException;
use App\Functions\UserUnlockedFeatureHelpers;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use App\Annotations\DoesNotRequireHouseHours;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/following")]
class FollowController extends AbstractController
{
    /**
     * @DoesNotRequireHouseHours()
     * @Route("", methods={"POST"})
     */
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function add(
        Request $request, ResponseService $responseService,
        EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();
        $followingId = $request->request->getInt('following');
        $note = $request->request->getString('note');

        if($followingId === $user->getId())
            throw new PSPInvalidOperationException('You can\'t follow yourself! That\'s so RANDOM! You\'re so RANDOM!');

        $following = $em->getRepository(User::class)->find($followingId);

        if(!$following)
            throw new PSPNotFoundException('Could not find a person with that number.');

        $alreadyFollowing = $em->getRepository(UserFollowing::class)->findOneBy([
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
            UserUnlockedFeatureHelpers::create($em, $user, UnlockableFeatureEnum::Florist);

        $em->flush();

        return $responseService->success();
    }
}
