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
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotFoundException;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/style")]
class RenameController
{
    #[Route("/{theme}/rename", methods: ["PATCH"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function renameTheme(
        UserStyle $theme, ResponseService $responseService, EntityManagerInterface $em,
        Request $request,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        if($theme->getUser()->getId() !== $user->getId())
            throw new PSPNotFoundException('That theme could not be found.');

        if($theme->getName() === UserStyle::Current)
            throw new PSPInvalidOperationException('That theme cannot be renamed!');

        $name = mb_trim($request->request->get('name'));

        if(strlen($name) < 1 || strlen($name) > 15)
            throw new PSPFormValidationException('Name must be between 1 and 15 characters.');

        $existingTheme = $em->getRepository(UserStyle::class)->findOneBy([
            'user' => $user,
            'name' => $name
        ]);

        if($existingTheme && $existingTheme->getId() !== $theme->getId())
            throw new PSPFormValidationException('You already have a theme named "' . $name . '".');

        $theme->setName($name);

        $em->flush();

        return $responseService->success([
            'name' => $name
        ]);
    }
}
