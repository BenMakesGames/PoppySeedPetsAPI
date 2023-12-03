<?php
namespace App\Service;

use App\Entity\User;
use App\Entity\UserSession;
use App\Functions\StringFunctions;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class SessionService
{
    private EntityManagerInterface $em;
    private TokenStorageInterface $tokenStorage;
    private ?string $currentSessionId;

    public function __construct(TokenStorageInterface $tokenStorage, EntityManagerInterface $em)
    {
        $this->tokenStorage = $tokenStorage;
        $this->em = $em;
    }

    public function generateSessionId(): string
    {
        do
        {
            $sessionId = StringFunctions::randomLettersAndNumbers(40);
        } while($this->sessionIdIsTaken($sessionId));

        return $sessionId;
    }

    private function sessionIdIsTaken(string $sessionId): bool
    {
        return $this->em->getRepository(UserSession::class)->count([ 'sessionId' => $sessionId ]) > 0;
    }

    public function setCurrentSession(string $userSessionId)
    {
        $this->currentSessionId = $userSessionId;
    }

    public function logIn(User $user, ?int $hours = null): UserSession
    {
        $user->setLastActivity();

        $sessionId = $this->generateSessionId();

        $userSession = (new UserSession())
            ->setSessionId($sessionId)
            ->setUser($user)
            ->setSessionExpiration($hours)
        ;

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

    public static function clearCookie()
    {
        if($_ENV['APP_ENV'] === 'dev')
            setcookie('sessionId', '', time() - 60 * 60, '/', 'localhost', false, true);
        else
            setcookie('sessionId', '', time() - 60 * 60, '/', 'poppyseedpets.com', true, true);
    }
}