<?php
namespace App\Controller\Account;

use App\Entity\User;
use App\Entity\UserLink;
use App\Enum\LocationEnum;
use App\Enum\PetLocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Enum\UserLinkVisibilityEnum;
use App\Repository\InventoryRepository;
use App\Repository\PetRepository;
use App\Repository\UserFollowingRepository;
use App\Repository\UserLinkRepository;
use App\Repository\UserStyleRepository;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Annotations\DoesNotRequireHouseHours;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @Route("/account")
 */
class GetByIdController extends AbstractController
{
    /**
     * @DoesNotRequireHouseHours()
     * @Route("/{user}", methods={"GET"}, requirements={"user"="\d+"})
     */
    public function getProfile(
        User $user, ResponseService $responseService, PetRepository $petRepository, InventoryRepository $inventoryRepository,
        NormalizerInterface $normalizer, UserStyleRepository $userStyleRepository, UserLinkRepository $userLinkRepository,
        UserFollowingRepository $userFollowingRepository
    )
    {
        $pets = $petRepository->findBy([ 'owner' => $user, 'location' => PetLocationEnum::HOME ]);
        $theme = $userStyleRepository->findCurrent($user);

        $data = [
            'user' => $normalizer->normalize($user, null, [ 'groups' => [ SerializationGroupEnum::USER_PUBLIC_PROFILE ] ]),
            'pets' => $normalizer->normalize($pets, null, [ 'groups' => [ SerializationGroupEnum::USER_PUBLIC_PROFILE ] ]),
            'theme' => $normalizer->normalize($theme, null, [ 'groups' => [ SerializationGroupEnum::PUBLIC_STYLE ]]),
        ];

        if((new \DateTimeImmutable())->format('m') == 12 && $user->getFireplace())
        {
            $data['stocking'] = $user->getFireplace()->getStocking();
        }

        $data['links'] = $this->getLinks($user, $userFollowingRepository, $userLinkRepository);

        if($user->getUnlockedFireplace())
        {
            $mantle = $inventoryRepository->findBy(['owner' => $user, 'location' => LocationEnum::MANTLE]);

            $data['mantle'] = $normalizer->normalize($mantle, null, [ 'groups' => [ SerializationGroupEnum::FIREPLACE_MANTLE ] ]);
        }

        return $responseService->success($data);
    }

    private function getLinks(User $user, UserFollowingRepository $userFollowingRepository, UserLinkRepository $userLinkRepository)
    {
        /** @var User|null $currentUser */
        $currentUser = $this->getUser();

        if(!$currentUser)
            return [];

        $showPrivateLinks =
            $user->getId() == $currentUser->getId() ||
            $userFollowingRepository->count([
                'user' => $user,
                'following' => $currentUser,
            ]) > 0
        ;

        if($showPrivateLinks)
        {
            $links = $userLinkRepository->findBy([ 'user' => $user ]);
        }
        else
        {
            $links = $userLinkRepository->findBy([
                'user' => $user,
                'visibility' => UserLinkVisibilityEnum::FOLLOWED,
            ]);
        }

        return array_map(fn(UserLink $link) => [
            'website' => $link->getWebsite(),
            'nameOrId' => $link->getNameOrId(),
        ], $links);
    }

    /**
     * @DoesNotRequireHouseHours()
     * @Route("/{user}/minimal", methods={"GET"}, requirements={"user"="\d+"})
     */
    public function getProfileMinimal(User $user, ResponseService $responseService)
    {
        return $responseService->success($user, [ SerializationGroupEnum::USER_PUBLIC_PROFILE ]);
    }
}
