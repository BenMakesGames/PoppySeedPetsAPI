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


namespace App\Controller\Park;

use App\Entity\Pet;
use App\Entity\User;
use App\Enum\ParkEventTypeEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPPetNotFoundException;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/park")]
class SignUpPetController extends AbstractController
{
    #[Route("/signUpPet/{pet}", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function changePetParkEventType(Pet $pet, Request $request, EntityManagerInterface $em, ResponseService $responseService)
    {
        $parkEventType = trim($request->request->getString('parkEventType'));

        if($parkEventType === '') $parkEventType = null;

        if($parkEventType !== null && !ParkEventTypeEnum::isAValue($parkEventType))
            throw new PSPFormValidationException('"' . $parkEventType . '" is not a valid park event type!');

        /** @var User $user */
        $user = $this->getUser();

        if($pet->getOwner()->getId() !== $user->getId())
            throw new PSPPetNotFoundException();

        $pet->setParkEventType($parkEventType);

        $em->flush();

        return $responseService->success();
    }
}