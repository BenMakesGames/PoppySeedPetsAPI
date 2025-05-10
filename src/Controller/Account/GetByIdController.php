<?php
declare(strict_types=1);

/**
 * This file is part of the Poppy Seed Pets API.
 *
 * The Poppy Seed Pets API is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets API is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets API. If not, see <https://www.gnu.org/licenses/>.
 */


namespace App\Controller\Account;

use App\Attributes\DoesNotRequireHouseHours;
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
use App\Service\UserAccessor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route("/account")]
class GetByIdController
{
    #[DoesNotRequireHouseHours]
    #[Route("/{user}", methods: ["GET"], requirements: ["user" => "\d+"])]
    public function getProfile(
        User $user, ResponseService $responseService, NormalizerInterface $normalizer, EntityManagerInterface $em,
        UserAccessor $userAccessor
    ): JsonResponse
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

        $data['links'] = $this->getLinks($userAccessor, $user, $em);

        if($user->hasUnlockedFeature(UnlockableFeatureEnum::Fireplace))
        {
            $mantle = $em->getRepository(Inventory::class)->findBy(['owner' => $user, 'location' => LocationEnum::MANTLE]);

            $data['mantle'] = $normalizer->normalize($mantle, null, [ 'groups' => [ SerializationGroupEnum::FIREPLACE_MANTLE ] ]);
        }

        return $responseService->success($data);
    }

    private function getLinks(UserAccessor $userAccessor, User $user, EntityManagerInterface $em): array
    {
        $currentUser = $userAccessor->getUser();

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

    #[DoesNotRequireHouseHours]
    #[Route("/{user}/minimal", methods: ["GET"], requirements: ["user" => "\d+"])]
    public function getProfileMinimal(User $user, ResponseService $responseService): JsonResponse
    {
        return $responseService->success($user, [ SerializationGroupEnum::USER_PUBLIC_PROFILE ]);
    }
}
