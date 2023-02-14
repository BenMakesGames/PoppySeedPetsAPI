<?php
namespace App\Service;

use App\Entity\User;
use App\Entity\UserSession;
use App\Functions\StringFunctions;
use App\Repository\UserSessionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class SessionService
{
    private UserSessionRepository $userSessionRepository;
    private EntityManagerInterface $em;
    private TokenStorageInterface $tokenStorage;
    private ?UserSession $currentSession;

    public function __construct(
        UserSessionRepository $userSessionRepository, TokenStorageInterface $tokenStorage,
        EntityManagerInterface $em
    )
    {
        $this->userSessionRepository = $userSessionRepository;
        $this->tokenStorage = $tokenStorage;
        $this->em = $em;
    }

    public function generateSessionId(): string
    {
        do
        {
            $sessionId = StringFunctions::randomLettersAndNumbers(40);
        } while($this->userSessionRepository->findOneBySessionId($sessionId) !== null);

        return $sessionId;
    }

    public function setCurrentSession(UserSession $userSession)
    {
        $this->currentSession = $userSession;
    }

    public function getCurrentSession(): ?UserSession
    {
        return $this->currentSession;
    }

    public function logIn(User $user, ?int $hours = null): UserSession
    {
        $user->setLastActivity();

        $userSession = (new UserSession())
            ->setSessionId($this->generateSessionId())
            ->setUser($user)
            ->setSessionExpiration($hours)
        ;

        $this->em->persist($userSession);

        $this->setCurrentSession($userSession);

        $this->tokenStorage->setToken(new UsernamePasswordToken($user, null, 'main', $user->getRoles()));

        return $userSession;
    }

    public function logOut(): bool
    {
        if(!$this->currentSession)
            return false;

        $this->em->remove($this->currentSession);

        $this->tokenStorage->setToken(null);

        return true;
    }
}