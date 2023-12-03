<?php
namespace App\Controller\Account;

use App\Entity\Inventory;
use App\Entity\Pet;
use App\Entity\User;
use App\Entity\UserFollowing;
use App\Entity\UserLink;
use App\Enum\LocationEnum;
use App\Enum\PetLocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Enum\UserLinkVisibilityEnum;
use App\Functions\UserStyleFunctions;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Annotations\DoesNotRequireHouseHours;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route("/account")]
class GetByIdController extends AbstractController
{
    /**
     * @DoesNotRequireHouseHours()
     * @Route("/{user}", methods={"GET"}, requirements={"user"="\d+"})
     */
    public function getProfile(
        User $user, ResponseService $responseService, NormalizerInterface $normalizer, EntityManagerInterface $em
    )
    {
        $pets = $em->getRepository(Pet::class)->findBy([ 'owner' => $user, 'location' => PetLocationEnum::HOME ]);
        $theme = UserStyleFunctions::findCurrent($em, $user->getId());

        $data = [
            'user' => $normalizer->normalize($user, null, [ 'groups' => [ SerializationGroupEnum::USER_PUBLIC_PROFILE ] ]),
            'pets' => $normalizer->normalize($pets, null, [ 'groups' => [ SerializationGroupEnum::USER_PUBLIC_PROFILE ] ]),
            'theme' => $normalizer->normalize($theme, null, [ 'groups' => [ SerializationGroupEnum::PUBLIC_STYLE ]]),
        ];

        if((new \DateTimeImmutable())->format('m') == 12 && $user->getFireplace())
        {
            $data['stocking'] = $user->getFireplace()->getStocking();
        }

        $data['links'] = $this->getLinks($user, $em);

        if($user->hasUnlockedFeature(UnlockableFeatureEnum::Fireplace))
        {
            $mantle = $em->getRepository(Inventory::class)->findBy(['owner' => $user, 'location' => LocationEnum::MANTLE]);

            $data['mantle'] = $normalizer->normalize($mantle, null, [ 'groups' => [ SerializationGroupEnum::FIREPLACE_MANTLE ] ]);
        }

        return $responseService->success($data);
    }

    private function getLinks(User $user, EntityManagerInterface $em)
    {
        /** @var User|null $currentUser */
        $currentUser = $this->getUser();

        if(!$currentUser)
            return [];

        $showPrivateLinks =
            $user->getId() == $currentUser->getId() ||
            $em->getRepository(UserFollowing::class)->count([
                'user' => $user,
                'following' => $currentUser,
            ]) > 0
        ;

        if($showPrivateLinks)
        {
            $links = $em->getRepository(UserLink::class)->findBy([ 'user' => $user ]);
        }
        else
        {
            $links = $em->getRepository(UserLink::class)->findBy([
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
