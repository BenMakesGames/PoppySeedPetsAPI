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

use App\Entity\Fireplace;
use App\Entity\User;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPNotUnlockedException;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/fireplace")]
class UpdateStockingController
{
    #[Route("/stocking", methods: ["PATCH"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function saveStockingSettings(
        Request $request, ResponseService $responseService, EntityManagerInterface $em,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Fireplace) || !$user->getFireplace())
            throw new PSPNotUnlockedException('Fireplace');

        $appearance = $request->request->getAlnum('appearance');
        $colorA = $request->request->getAlnum('colorA');
        $colorB = $request->request->getAlnum('colorB');

        if(!in_array($appearance, Fireplace::STOCKING_APPEARANCES))
            throw new PSPFormValidationException('Must choose a stocking appearance...');

        if(!preg_match('/[A-Fa-f0-9]{6}/', $colorA))
            throw new PSPFormValidationException('Color A is not valid.');

        if(!preg_match('/[A-Fa-f0-9]{6}/', $colorB))
            throw new PSPFormValidationException('Color B is not valid.');

        $user->getFireplace()
            ->setStockingAppearance($appearance)
            ->setStockingColorA($colorA)
            ->setStockingColorB($colorB)
        ;

        $em->flush();

        return $responseService->success();
    }
}
