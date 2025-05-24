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


namespace App\Controller\Fireplace;

use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPNotUnlockedException;
use App\Functions\DragonRepository;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use App\Service\UserAccessor;

#[Route("/fireplace")]
class FireplaceController
{
    #[Route("", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getFireplace(
        ResponseService $responseService, EntityManagerInterface $em,
        NormalizerInterface $normalizer,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Fireplace) || !$user->getFireplace())
            throw new PSPNotUnlockedException('Fireplace');

        $mantle = $em->getRepository(Inventory::class)->findBy([
            'owner' => $user,
            'location' => LocationEnum::Mantle
        ]);

        $dragon = DragonRepository::findWhelp($em, $user);

        return $responseService->success(
            [
                'mantle' => $normalizer->normalize($mantle, null, [ 'groups' => [ SerializationGroupEnum::MY_INVENTORY ] ]),
                'fireplace' => $normalizer->normalize($user->getFireplace(), null, [ 'groups' => [ SerializationGroupEnum::MY_FIREPLACE, SerializationGroupEnum::HELPER_PET ] ]),
                'whelp' => $normalizer->normalize($dragon, null, [ 'groups' => [ SerializationGroupEnum::MY_FIREPLACE ] ]),
            ]
        );
    }

    #[Route("/mantle/{user}", methods: ["GET"], requirements: [ "user" => "\d+" ])]
    public function getMantle(
        User $user, EntityManagerInterface $em, ResponseService $responseService
    ): JsonResponse
    {
        $inventory = $em->getRepository(Inventory::class)->findBy([
            'owner' => $user,
            'location' => LocationEnum::Mantle
        ]);

        return $responseService->success($inventory, [ SerializationGroupEnum::FIREPLACE_MANTLE ]);
    }
}
