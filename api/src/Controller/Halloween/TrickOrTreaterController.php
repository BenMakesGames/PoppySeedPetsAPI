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

namespace App\Controller\Halloween;

use App\Enum\SerializationGroupEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotFoundException;
use App\Functions\CalendarFunctions;
use App\Functions\UserQuestRepository;
use App\Service\Clock;
use App\Service\Holidays\HalloweenService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use App\Service\UserAccessor;

#[Route("/halloween")]
class TrickOrTreaterController
{
    #[Route("/trickOrTreater", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getTrickOrTreater(
        ResponseService $responseService, EntityManagerInterface $em, HalloweenService $halloweenService,
        NormalizerInterface $normalizer, Clock $clock,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        if(!CalendarFunctions::isHalloween($clock->now))
            throw new PSPInvalidOperationException('It isn\'t Halloween!');

        $nextTrickOrTreater = $halloweenService->getNextTrickOrTreater($user);

        if((new \DateTimeImmutable())->format('Y-m-d H:i:s') < $nextTrickOrTreater->getValue())
        {
            return $responseService->success([
                'trickOrTreater' => null,
                'nextTrickOrTreater' => $nextTrickOrTreater->getValue(),
                'totalCandyGiven' => UserQuestRepository::findOrCreate($em, $user, 'Trick-or-Treaters Treated', 0)->getValue()
            ]);
        }

        $trickOrTreater = $halloweenService->getTrickOrTreater($user);

        $em->flush();

        if($trickOrTreater === null)
            throw new PSPNotFoundException('No one else\'s pets are trick-or-treating right now! (Not many people must be playing :| TELL YOUR FRIENDS TO SIGN IN AND DRESS UP THEIR PETS!)');

        return $responseService->success([
            'trickOrTreater' => $normalizer->normalize($trickOrTreater, null, [ 'groups' => [ SerializationGroupEnum::PET_PUBLIC_PROFILE ] ]),
            'nextTrickOrTreater' => $nextTrickOrTreater->getValue(),
            'candy' => $normalizer->normalize($halloweenService->getCandy($user), null, [ 'groups' => [ SerializationGroupEnum::MY_INVENTORY ] ]),
            'totalCandyGiven' => UserQuestRepository::findOrCreate($em, $user, 'Trick-or-Treaters Treated', 0)->getValue()
        ]);
    }
}
