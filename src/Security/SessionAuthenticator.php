<?php
namespace App\Security;

use App\Repository\UserSessionRepository;
use App\Service\ResponseService;
use App\Service\SessionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class SessionAuthenticator extends AbstractAuthenticator
{
    private EntityManagerInterface $em;
    private UserSessionRepository $userSessionRepository;
    private ResponseService $responseService;
    private SessionService $sessionService;

    public function __construct(
        EntityManagerInterface $em, UserSessionRepository $userSessionRepository,
        ResponseService $responseService, SessionService $sessionService
    )
    {
        $this->em = $em;
        $this->userSessionRepository = $userSessionRepository;
        $this->responseService = $responseService;
        $this->sessionService = $sessionService;
    }

    public function supports(Request $request): bool
    {
        return $request->headers->has('Authorization') && substr($request->headers->get('Authorization'), 0, 7) === 'Bearer ';
    }

    public function authenticate(Request $request): Passport
    {
        $sessionId = substr($request->headers->get('Authorization'), 7);

        if (!$sessionId) {
            throw new CustomUserMessageAuthenticationException();
        }

        $session = $this->userSessionRepository->findOneBySessionId($sessionId);

        if(!$session || $session->getSessionExpiration() < new \DateTimeImmutable())
        {
            $this->responseService->setSessionId(null);
            throw new AccessDeniedHttpException('You have been logged out due to inactivity. Please log in again.');
        }

        $user = $session->getUser();

        if($user->getIsLocked())
        {
            $this->responseService->setSessionId(null);
            throw new AccessDeniedHttpException('This account has been locked.');
        }

        $this->sessionService->setCurrentSession($session);

        $session->setSessionExpiration();
        $user->setLastActivity();
        $this->em->flush();

        return new SelfValidatingPassport(new UserBadge($user->getEmail()));
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
