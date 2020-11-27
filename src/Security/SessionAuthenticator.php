<?php
namespace App\Security;

use App\Repository\UserRepository;
use App\Repository\UserSessionRepository;
use App\Service\HouseService;
use App\Service\ResponseService;
use App\Service\SessionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class SessionAuthenticator extends AbstractGuardAuthenticator
{
    private $userRepository;
    private $em;
    private $houseService;
    private $userSessionRepository;
    private $responseService;
    private $sessionService;

    public function __construct(
        UserRepository $userRepository, EntityManagerInterface $em, HouseService $houseService,
        UserSessionRepository $userSessionRepository, ResponseService $responseService, SessionService $sessionService
    )
    {
        $this->userRepository = $userRepository;
        $this->em = $em;
        $this->houseService = $houseService;
        $this->userSessionRepository = $userSessionRepository;
        $this->responseService = $responseService;
        $this->sessionService = $sessionService;
    }

    public function supports(Request $request)
    {
        return $request->headers->has('Authorization') && substr($request->headers->get('Authorization'), 0, 7) === 'Bearer ';
    }

    public function getCredentials(Request $request)
    {
        return [
            'sessionId' => substr($request->headers->get('Authorization'), 7)
        ];
    }

    public function getUser($credentials, UserProviderInterface $userProvider): ?UserInterface
    {
        $sessionId = $credentials['sessionId'];

        if (!$sessionId) {
            return null;
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

        return $user;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey)
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $data = [
            'success' => false,
            'errors' => [ strtr($exception->getMessageKey(), $exception->getMessageData()) ]
        ];

        return new JsonResponse($data, Response::HTTP_FORBIDDEN);
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        $data = [
            'success' => false,
            'errors' => [ 'Authentication Required' ]
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    public function supportsRememberMe()
    {
        return false;
    }

}
