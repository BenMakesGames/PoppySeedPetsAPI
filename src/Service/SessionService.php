<?php
namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;

class SessionService
{
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
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

    public function logIn(User $user)
    {
        $user
            ->setLastActivity()
            ->setSessionId($this->getSessionId())
        ;
    }
}