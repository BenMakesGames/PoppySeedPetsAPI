<?php
namespace App\Security;

use App\Repository\UserSessionRepository;
use App\Service\PerformanceProfiler;
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
    private EntityManagerInterface $em;
    private UserSessionRepository $userSessionRepository;
    private ResponseService $responseService;
    private SessionService $sessionService;
    private PerformanceProfiler $performanceProfiler;

    public function __construct(
        EntityManagerInterface $em, UserSessionRepository $userSessionRepository, ResponseService $responseService,
        SessionService $sessionService, PerformanceProfiler $performanceProfiler
    )
    {
        $this->em = $em;
        $this->userSessionRepository = $userSessionRepository;
        $this->responseService = $responseService;
        $this->sessionService = $sessionService;
        $this->performanceProfiler = $performanceProfiler;
    }

    public function supports(Request $request): bool
    {
        return $request->headers->has('Authorization') && substr($request->headers->get('Authorization'), 0, 7) === 'Bearer ';
    }

    private static function getSessionIdOrThrow(Request $request): string
    {
        $sessionId = substr($request->headers->get('Authorization'), 7);

        if (!$sessionId) {
            throw new CustomUserMessageAuthenticationException();
        }

        return $sessionId;
    }

    public function authenticate(Request $request): Passport
    {
        $time = microtime(true);

        $sessionId = self::getSessionIdOrThrow($request);

        $session = $this->userSessionRepository->findOneBySessionId($sessionId);

        if(!$session || $session->getSessionExpiration() < new \DateTimeImmutable())
        {
            $this->responseService->setSessionId(null);
            $this->performanceProfiler->logExecutionTime(__METHOD__ . ' - session expired', microtime(true) - $time);
            throw new UnauthorizedHttpException('You have been logged out due to inactivity. Please log in again.');
        }

        $user = $session->getUser();

        if($user->getIsLocked())
        {
            $this->responseService->setSessionId(null);
            // technically, this should be a Forbidden exception, because we know who the user is,
            // but the client is programmed to auto log a user out when they receive a 401 (Unauthorized).
            // there are legit reasons a user might be Forbidden that we DON'T want them to be logged
            // out for (ex: accessing the Fireplace before they unlocked it).

            $this->performanceProfiler->logExecutionTime(__METHOD__ . ' - is locked', microtime(true) - $time);

            throw new UnauthorizedHttpException('This account has been locked.');
        }

        $this->sessionService->setCurrentSession($sessionId);

        $session->setSessionExpiration();
        $user->setLastActivity();
        $this->em->flush();

        $response = new SelfValidatingPassport(new UserBadge($user->getEmail()));

        $this->performanceProfiler->logExecutionTime(__METHOD__ . ' - success', microtime(true) - $time);

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
