<?php
namespace App\Controller\Account;

use App\Entity\PassphraseResetRequest;
use App\Entity\User;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotFoundException;
use App\Functions\PlayerLogHelpers;
use App\Service\PassphraseResetService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Annotations\DoesNotRequireHouseHours;

#[Route("/account")]
class SecurityController extends AbstractController
{
    /**
     * @DoesNotRequireHouseHours()
     * @Route("/updateEmail", methods={"POST"})
     */
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function updateEmail(
        Request $request, ResponseService $responseService, UserPasswordHasherInterface $passwordEncoder,
        EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();
        $oldEmail = $user->getEmail();

        if(!$passwordEncoder->isPasswordValid($user, $request->request->get('confirmPassphrase')))
            throw new AccessDeniedHttpException('Passphrase is not correct.');

        $newEmail = trim($request->request->get('newEmail'));

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

        PlayerLogHelpers::create(
            $em,
            $user,
            'You changed your e-mail address, from `' . $oldEmail . '` to `' . $newEmail .'`.',
            [ 'Account & Security' ]
        );

        $em->flush();

        return $responseService->success();
    }

    /**
     * @DoesNotRequireHouseHours()
     * @Route("/updatePassphrase", methods={"POST"})
     */
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function updatePassphrase(
        Request $request, ResponseService $responseService, UserPasswordHasherInterface $passwordEncoder,
        EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        if(!$passwordEncoder->isPasswordValid($user, $request->request->get('confirmPassphrase')))
            throw new AccessDeniedHttpException('Passphrase is not correct.');

        $newPassphrase = trim($request->request->get('newPassphrase'));

        if(\mb_strlen($newPassphrase) < 10)
            throw new PSPFormValidationException('Passphrase must be at least 10 characters long.');

        $user->setPassword($passwordEncoder->hashPassword($user, $newPassphrase));

        PlayerLogHelpers::create(
            $em,
            $user,
            'You changed your passphrase.',
            [ 'Account & Security' ]
        );

        $em->flush();

        return $responseService->success();
    }

    /**
     * @DoesNotRequireHouseHours()
     * @Route("/requestPassphraseReset", methods={"POST"})
     */
    public function requestPassphraseReset(
        Request $request, EntityManagerInterface $em, ResponseService $responseService,
        PassphraseResetService $passphraseResetService
    )
    {
        $email = trim($request->request->get('email', ''));

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

    /**
     * @DoesNotRequireHouseHours()
     * @Route("/requestPassphraseReset/{code}", methods={"POST"})
     */
    public function resetPassphrase(
        string $code, Request $request, UserPasswordHasherInterface $userPasswordEncoder, EntityManagerInterface $em,
        ResponseService $responseService
    )
    {
        $passphrase = trim($request->request->get('passphrase', ''));

        if(\mb_strlen($passphrase) < 10)
            throw new PSPFormValidationException('Passphrase must be at least 10 characters long. (Pro tip: try using an actual phrase, or short sentence!)');

        $resetRequest = $em->getRepository(PassphraseResetRequest::class)->findOneBy([ 'code' => $code ]);

        if(!$resetRequest || $resetRequest->getExpiresOn() <= new \DateTimeImmutable())
            throw new PSPNotFoundException('This reset URL is invalid, or expired.');

        $user = $resetRequest->getUser();

        $user->setPassword($userPasswordEncoder->hashPassword($user, $passphrase));

        $em->remove($resetRequest);

        PlayerLogHelpers::create(
            $em,
            $user,
            'You reset your passphrase.',
            [ 'Account & Security' ]
        );

        $em->flush();

        return $responseService->success();
    }
}
