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


namespace App\Controller\House;

use App\Attributes\DoesNotRequireHouseHours;
use App\Entity\Inventory;
use App\Entity\Pet;
use App\Enum\LocationEnum;
use App\Enum\PetLocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Service\HouseService;
use App\Service\QualityTimeService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/house")]
class DoQualityTimeController
{
    #[DoesNotRequireHouseHours]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    #[Route("/doQualityTime", methods: ["POST"])]
    public function doQualityTime(
        ResponseService $responseService, EntityManagerInterface $em, QualityTimeService $qualityTimeService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        if($user->getLastPerformedQualityTime() >= (new \DateTimeImmutable())->modify('-4 hours'))
            throw new PSPInvalidOperationException('You cannot do quality time more than once every 4 hours. (Just like in real life???)');

        $message = $qualityTimeService->doQualityTime($user);

        $em->flush();

        $responseService->setReloadPets();

        return $responseService->success([
            'message' => $message,
        ]);
    }
}
