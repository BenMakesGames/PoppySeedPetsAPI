<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserAccessor
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage
    )
    {
    }

    public function getUser(): ?User
    {
        $user = $this->tokenStorage->getToken()?->getUser();

        if(!($user instanceof User))
            return null;

        return $user;
    }

    public function getUserOrThrow(): User
    {
        $user = $this->tokenStorage->getToken()?->getUser();

        if(!($user instanceof User))
            throw new UnauthorizedHttpException('You must be logged in to do that!');

        return $user;
    }
}