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
use App\Enum\SerializationGroupEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Functions\UserStyleFunctions;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/style")]
class SetCurrentController
{
    #[Route("/{theme}/setCurrent", methods: ["PATCH"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function setCurrent(
        UserStyle $theme, ResponseService $responseService, EntityManagerInterface $em,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        if($theme->getName() === UserStyle::Current)
            throw new PSPInvalidOperationException('You\'re already using that theme!');

        $current = UserStyleFunctions::findCurrent($em, $user->getId());

        if(!$current)
        {
            $current = new UserStyle(user: $user, name: UserStyle::Current);

            $em->persist($current);
        }

        foreach(UserStyle::Properties as $property)
            $current->{'set' . $property}($theme->{'get' . $property}());

        $em->flush();

        return $responseService->success($current, [ SerializationGroupEnum::MY_STYLE ]);
    }
}
