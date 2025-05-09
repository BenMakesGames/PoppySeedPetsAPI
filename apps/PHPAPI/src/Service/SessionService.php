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
use App\Entity\UserSession;
use App\Functions\StringFunctions;
use App\Security\CryptographicFunctions;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class SessionService
{
    private ?string $currentSessionId;

    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly EntityManagerInterface $em
    )
    {
    }

    public function generateSessionId(): string
    {
        do
        {
            $sessionId = CryptographicFunctions::generateSecureRandomString(40);
        } while($this->sessionIdIsTaken($sessionId));

        return $sessionId;
    }

    private function sessionIdIsTaken(string $sessionId): bool
    {
        return $this->em->getRepository(UserSession::class)->count([ 'sessionId' => $sessionId ]) > 0;
    }

    public function setCurrentSession(string $userSessionId): void
    {
        $this->currentSessionId = $userSessionId;
    }

    public function logIn(User $user, ?int $hours = null): UserSession
    {
        $user->setLastActivity();

        $sessionId = $this->generateSessionId();

        $userSession = new UserSession(
            user: $user,
            sessionId: $sessionId,
            hoursToExpiration: $hours ?? $user->getDefaultSessionLengthInHours()
        );

        $this->em->persist($userSession);

        $this->setCurrentSession($sessionId);

        $this->tokenStorage->setToken(new UsernamePasswordToken($user, 'main', $user->getRoles()));

        return $userSession;
    }

    public function logOut(): bool
    {
        if(!$this->currentSessionId)
            return false;

        $currentSession = $this->em->getRepository(UserSession::class)->findOneBy([ 'sessionId' => $this->currentSessionId ]);

        if(!$currentSession)
            return false;

        $this->em->remove($currentSession);
        $this->currentSessionId = null;
        $this->tokenStorage->setToken(null);

        self::clearCookie();

        return true;
    }

    public static function clearCookie(): void
    {
        if($_ENV['APP_ENV'] === 'dev')
            setcookie('sessionId', '', time() - 60 * 60, '/', 'localhost', false, true);
        else
            setcookie('sessionId', '', time() - 60 * 60, '/', 'poppyseedpets.com', true, true);
    }
}