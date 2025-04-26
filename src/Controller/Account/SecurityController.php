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


namespace App\Controller\Account;

use App\Attributes\DoesNotRequireHouseHours;
use App\Entity\PassphraseResetRequest;
use App\Entity\User;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotFoundException;
use App\Functions\PlayerLogFactory;
use App\Service\PassphraseResetService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/account")]
class SecurityController
{
    #[DoesNotRequireHouseHours]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    #[Route("/updateEmail", methods: ["POST"])]
    public function updateEmail(
        Request $request, ResponseService $responseService, UserPasswordHasherInterface $passwordEncoder,
        EntityManagerInterface $em,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();
        $oldEmail = $user->getEmail();

        if(!$passwordEncoder->isPasswordValid($user, $request->request->getString('confirmPassphrase')))
            throw new AccessDeniedHttpException('Passphrase is not correct.');

        $newEmail = trim($request->request->getString('newEmail'));

        if($newEmail === '' || !filter_var($newEmail, FILTER_VALIDATE_EMAIL))
            throw new PSPFormValidationException('Email address is not valid.');

        if(strtoupper($oldEmail) == strtoupper($newEmail))
            throw new PSPInvalidOperationException('B-- but that\'s _already_ your email address...');

        if(str_ends_with($newEmail, '@poppyseedpets.com') || str_ends_with($newEmail, '.poppyseedpets.com'))
            throw new PSPFormValidationException('poppyseedpets.com e-mail addresses cannot be used.');

        $alreadyInUse = $em->getRepository(User::class)->findOneBy([ 'email' => $newEmail ]);

        if($alreadyInUse && $alreadyInUse->getId() != $user->getId())
            throw new PSPFormValidationException('That e-mail address is already in use.');

        $user->setEmail($newEmail);

        PlayerLogFactory::create(
            $em,
            $user,
            'You changed your e-mail address, from `' . $oldEmail . '` to `' . $newEmail .'`.',
            [ 'Account & Security' ]
        );

        $em->flush();

        return $responseService->success();
    }

    #[DoesNotRequireHouseHours]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    #[Route("/updatePassphrase", methods: ["POST"])]
    public function updatePassphrase(
        Request $request, ResponseService $responseService, UserPasswordHasherInterface $passwordEncoder,
        EntityManagerInterface $em,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        if(!$passwordEncoder->isPasswordValid($user, $request->request->getString('confirmPassphrase')))
            throw new AccessDeniedHttpException('Passphrase is not correct.');

        $newPassphrase = mb_trim($request->request->getString('newPassphrase'));

        if(\mb_strlen($newPassphrase) < User::MinPassphraseLength)
            throw new PSPFormValidationException('Passphrase must be at least ' . User::MinPassphraseLength . ' characters long. (Pro tip: try using an actual phrase, or short sentence!)');

        if(\mb_strlen($newPassphrase) > User::MaxPassphraseLength)
            throw new PSPFormValidationException('Passphrase must not exceed ' . User::MaxPassphraseLength . ' characters.');

        $user->setPassword($passwordEncoder->hashPassword($user, $newPassphrase));

        PlayerLogFactory::create(
            $em,
            $user,
            'You changed your passphrase.',
            [ 'Account & Security' ]
        );

        $em->flush();

        return $responseService->success();
    }

    #[DoesNotRequireHouseHours]
    #[Route("/requestPassphraseReset", methods: ["POST"])]
    public function requestPassphraseReset(
        Request $request, EntityManagerInterface $em, ResponseService $responseService,
        PassphraseResetService $passphraseResetService
    ): JsonResponse
    {
        $email = trim($request->request->getString('email'));

        if($email === '')
            throw new PSPFormValidationException('E-mail address is required.');

        if(!filter_var($email, FILTER_VALIDATE_EMAIL))
            throw new PSPFormValidationException('E-mail address is invalid.');

        $user = $em->getRepository(User::class)->findOneBy([ 'email' => $email ]);

        if(!$user)
            throw new PSPNotFoundException('There is no user with that e-mail address.');

        $passphraseResetService->requestReset($user);

        return $responseService->success();
    }

    #[DoesNotRequireHouseHours]
    #[Route("/requestPassphraseReset/{code}", methods: ["POST"])]
    public function resetPassphrase(
        string $code, Request $request, UserPasswordHasherInterface $userPasswordEncoder, EntityManagerInterface $em,
        ResponseService $responseService
    ): JsonResponse
    {
        $passphrase = mb_trim($request->request->getString('passphrase'));

        if(\mb_strlen($passphrase) < User::MinPassphraseLength)
            throw new PSPFormValidationException('Passphrase must be at least ' . User::MinPassphraseLength . ' characters long. (Pro tip: try using an actual phrase, or short sentence!)');

        if(\mb_strlen($passphrase) > User::MaxPassphraseLength)
            throw new PSPFormValidationException('Passphrase must not exceed ' . User::MaxPassphraseLength . ' characters.');

        $resetRequest = $em->getRepository(PassphraseResetRequest::class)->findOneBy([ 'code' => $code ]);

        if(!$resetRequest || $resetRequest->getExpiresOn() <= new \DateTimeImmutable())
            throw new PSPNotFoundException('This reset URL is invalid, or expired.');

        $user = $resetRequest->getUser();

        $user->setPassword($userPasswordEncoder->hashPassword($user, $passphrase));

        $em->remove($resetRequest);

        PlayerLogFactory::create(
            $em,
            $user,
            'You reset your passphrase.',
            [ 'Account & Security' ]
        );

        $em->flush();

        return $responseService->success();
    }
}
