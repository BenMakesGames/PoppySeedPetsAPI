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

namespace App\Service;

use App\Entity\User;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserAccessor
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage
    )
    {
    }

    public function getUser(): ?User
    {
        $user = $this->tokenStorage->getToken()?->getUser();

        if(!($user instanceof User))
            return null;

        return $user;
    }

    public function getUserOrThrow(): User
    {
        $user = $this->tokenStorage->getToken()?->getUser();

        if(!($user instanceof User))
            throw new UnauthorizedHttpException('You must be logged in to do that!');

        if($user->isDisabled() || $user->getIsLocked())
            throw new UnauthorizedHttpException('This account is not active.');

        return $user;
    }
}