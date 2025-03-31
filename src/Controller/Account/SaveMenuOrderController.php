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


namespace App\Controller\Account;

use App\Attributes\DoesNotRequireHouseHours;
use App\Entity\User;
use App\Exceptions\PSPFormValidationException;
use App\Functions\UserMenuFunctions;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/account")]
class SaveMenuOrderController extends AbstractController
{
    #[Route("/menuOrder", methods: ["PATCH"])]
    #[DoesNotRequireHouseHours]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function saveMenuOrder(
        Request $request, EntityManagerInterface $em, ResponseService $responseService
    )
    {
        /** @var User $user */
        $user = $this->getUser();
        $newOrder = $request->request->all('order');

        if(count($newOrder) === 0)
            throw new PSPFormValidationException('No order info was provided.');

        UserMenuFunctions::updateUserMenuSortOrder($em, $user, $newOrder);

        $em->flush();

        return $responseService->success();
    }
}
