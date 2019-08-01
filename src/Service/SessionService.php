<?php
namespace App\Service;

use App\Entity\User;
use App\Functions\StringFunctions;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class SessionService
{
    private $userRepository;
    private $tokenStorage;

    public function __construct(UserRepository $userRepository, TokenStorageInterface $tokenStorage)
    {
        $this->userRepository = $userRepository;
        $this->tokenStorage = $tokenStorage;
    }

    public function getSessionId(): string
    {
        do
        {
            $sessionId = StringFunctions::randomLettersAndNumbers(40);
        } while($this->userRepository->findOneBySessionId($sessionId) !== null);

        return $sessionId;
    }

    public function logIn(User $user, ?int $hours = null)
    {
        $user
            ->setLastActivity($hours)
            ->setSessionId($this->getSessionId())
        ;

        $this->tokenStorage->setToken(new UsernamePasswordToken($user, null, 'main', $user->getRoles()));
    }
}