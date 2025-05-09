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


namespace App\Controller\Style;

use App\Entity\UserStyle;
use App\Functions\UserStyleFunctions;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/style")]
class SaveCurrentController
{
    #[Route("/current", methods: ["PATCH"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function saveCurrentStyle(
        Request $request, EntityManagerInterface $em, ResponseService $responseService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        $style = UserStyleFunctions::findCurrent($em, $user->getId());

        if(!$style)
        {
            $style = new UserStyle(user: $user, name: UserStyle::Current);

            $em->persist($style);
        }

        foreach(UserStyle::Properties as $property)
        {
            $color = $request->request->get($property);

            if(!preg_match('/^#?[0-9a-fA-F]{6}$/', $color))
                continue;

            if(strlen($color) === 7)
                $color = substr($color, 1);

            $style->{'set' . $property}($color);
        }

        $em->flush();

        return $responseService->success();
    }
}
