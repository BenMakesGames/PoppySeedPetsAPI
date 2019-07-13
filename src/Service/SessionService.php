<?php
namespace App\Service;

use App\Entity\User;
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

    private function generateSessionId(): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        $string = '';

        for($i = 0; $i < 40; $i++)
        {
            $string .= $characters[mt_rand(0, strlen($characters) - 1)];
        }

        return $string;
    }

    public function getSessionId(): string
    {
        do
        {
            $sessionId = $this->generateSessionId();
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