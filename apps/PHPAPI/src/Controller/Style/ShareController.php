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

use App\Entity\User;
use App\Entity\UserStyle;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPInvalidOperationException;
use App\Functions\UserStyleFunctions;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/style")]
class ShareController extends AbstractController
{
    #[Route("", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function shareTheme(
        ResponseService $responseService, Request $request, EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();
        $name = mb_trim($request->request->get('name'));

        if(strlen($name) < 1 || strlen($name) > 15)
            throw new PSPFormValidationException('Name must be between 1 and 15 characters.');

        $current = UserStyleFunctions::findCurrent($em, $user->getId());

        if(!$current)
            throw new PSPInvalidOperationException('You have to save your current theme, first.');

        $theme = $em->getRepository(UserStyle::class)->findOneBy([ 'user' => $user, 'name' => $name ]);

        if(!$theme)
        {
            $numberOfThemes = UserStyleFunctions::countThemesByUser($em, $user);

            if($numberOfThemes >= 10)
                throw new PSPInvalidOperationException('You may not have more than 10 themes! Sorry...');

            $theme = new UserStyle(user: $user, name: $name);

            $em->persist($theme);
        }

        foreach(UserStyle::Properties as $property)
            $theme->{'set' . $property}($current->{'get' . $property}());

        $em->flush();

        return $responseService->success();
    }
}
