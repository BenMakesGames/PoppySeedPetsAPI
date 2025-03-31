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


namespace App\Security;

use App\Entity\UserSession;
use App\Exceptions\PSPAccountLocked;
use App\Exceptions\PSPSessionExpired;
use App\Service\ResponseService;
use App\Service\SessionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class SessionAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ResponseService $responseService,
        private readonly SessionService $sessionService
    )
    {
    }

    public function supports(Request $request): bool
    {
        if($request->cookies->has('sessionId') && strlen($request->cookies->get('sessionId')) === 40)
            return true;

        return $request->headers->has('Authorization') && substr($request->headers->get('Authorization'), 0, 7) === 'Bearer ';
    }

    private static function getSessionIdOrThrow(Request $request): string
    {
        $sessionId = $request->cookies->has('sessionId')
            ? $request->cookies->get('sessionId')
            : substr($request->headers->get('Authorization'), 7);

        if (!$sessionId)
            throw new CustomUserMessageAuthenticationException();

        return $sessionId;
    }

    public function authenticate(Request $request): Passport
    {
        $sessionId = self::getSessionIdOrThrow($request);

        $session = $this->em->getRepository(UserSession::class)->findOneBy([ 'sessionId' => $sessionId ]);

        if(!$session || $session->getSessionExpiration() < new \DateTimeImmutable())
        {
            SessionService::clearCookie();
            $this->responseService->setSessionId(null);
            throw new PSPSessionExpired();
        }

        $user = $session->getUser();

        if($user->getIsLocked())
        {
            $this->responseService->setSessionId(null);
            throw new PSPAccountLocked();
        }

        $this->sessionService->setCurrentSession($sessionId);

        $session->setSessionExpiration();
        $user->setLastActivity();
        $this->em->flush();

        $response = new SelfValidatingPassport(new UserBadge($user->getEmail()));

        return $response;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $data = [
            'success' => false,
            'errors' => [ strtr($exception->getMessageKey(), $exception->getMessageData()) ]
        ];

        return new JsonResponse($data, Response::HTTP_FORBIDDEN);
    }

}
