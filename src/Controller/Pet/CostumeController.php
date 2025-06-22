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


namespace App\Controller\Pet;

use App\Entity\Pet;
use App\Enum\PetBadgeEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPPetNotFoundException;
use App\Functions\ActivityHelpers;
use App\Functions\PetBadgeHelpers;
use App\Functions\ProfanityFilterFunctions;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/pet")]
class CostumeController
{
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    #[Route("/{pet}/costume", methods: ["PATCH"], requirements: ["pet" => "\d+"])]
    public function setCostume(
        Pet $pet, Request $request, ResponseService $responseService, EntityManagerInterface $em,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        if($pet->getOwner()->getId() !== $user->getId())
            throw new PSPPetNotFoundException();

        $costume = mb_trim($request->request->get('costume'));

        if(\mb_strlen($costume) > 30)
            throw new PSPFormValidationException('Costume description cannot be longer than 30 characters.');

        $costume = ProfanityFilterFunctions::filter($costume);

        if(\mb_strlen($costume) > 30)
            $costume = \mb_substr($costume, 0, 30);

        $pet->setCostume($costume);

        PetBadgeHelpers::awardBadgeAndLog($em, $pet, PetBadgeEnum::WasGivenACostumeName, ActivityHelpers::UserName($user) . ' gave ' . ActivityHelpers::PetName($pet) . '\'s Halloween costume a name.');

        $em->flush();

        return $responseService->success();
    }
}
