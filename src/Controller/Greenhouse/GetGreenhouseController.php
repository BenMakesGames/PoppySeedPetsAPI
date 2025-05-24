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


namespace App\Controller\Greenhouse;

use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Exceptions\PSPNotUnlockedException;
use App\Functions\ItemRepository;
use App\Service\GreenhouseService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use App\Service\UserAccessor;

#[Route("/greenhouse")]
class GetGreenhouseController
{
    #[Route("", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getGreenhouse(
        ResponseService $responseService, GreenhouseService $greenhouseService,
        NormalizerInterface $normalizer, EntityManagerInterface $em,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        if(!$user->getGreenhouse())
            throw new PSPNotUnlockedException('Greenhouse');

        $greenhouseService->maybeAssignPollinators($user);

        $data = $normalizer->normalize($greenhouseService->getGreenhouseResponseData($user), null, [
            'groups' => [ SerializationGroupEnum::GREENHOUSE_PLANT, SerializationGroupEnum::MY_GREENHOUSE, SerializationGroupEnum::HELPER_PET ]
        ]);

        if($user->getGreenhouse()->getHasBirdBath())
        {
            $data['hasBubblegum'] = self::hasItemInBirdbath($em, $user, 'Bubblegum');
            $data['hasOil'] = self::hasItemInBirdbath($em, $user, 'Oil');
        }

        return $responseService->success($data);
    }

    private static function hasItemInBirdbath(EntityManagerInterface $em, User $user, string $itemName): bool
    {
        return $em->getRepository(Inventory::class)->count([
            'owner' => $user->getId(),
            'location' => LocationEnum::BirdBath,
            'item' => ItemRepository::getIdByName($em, $itemName)
        ]) > 0;
    }
}
