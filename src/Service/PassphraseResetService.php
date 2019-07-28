<?php
namespace App\Service;

use App\Entity\PassphraseResetRequest;
use App\Entity\User;
use App\Functions\StringFunctions;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class PassphraseResetService
{
    private $mailer;
    private $userRepository;
    private $em;

    public function __construct(\Swift_Mailer $mailer, UserRepository $userRepository, EntityManagerInterface $em)
    {
        $this->mailer = $mailer;
        $this->userRepository = $userRepository;
        $this->em = $em;
    }

    public function requestReset(User $user): bool
    {
        if(!$user)
            return false;

        $now = new \DateTimeImmutable();

        if($user->getPassphraseResetRequest())
        {
            if($user->getPassphraseResetRequest()->getExpiresOn() >= $now)
                return false;

            $user->getPassphraseResetRequest()
                ->setExpiresOn($now->modify('+8 hours'))
                ->setCode(StringFunctions::randomLettersAndNumbers(40))
            ;

            $this->em->flush();

            $this->sendPasswordResetEmail($user->getPassphraseResetRequest());

            return true;
        }
        else
        {
            $passwordResetRequest = (new PassphraseResetRequest())
                ->setUser($user)
                ->setExpiresOn($now->modify('+8 hours'))
                ->setCode(StringFunctions::randomLettersAndNumbers(40))
            ;

            $this->em->persist($passwordResetRequest);
            $this->em->flush();

            $this->sendPasswordResetEmail($passwordResetRequest);

            return true;
        }
    }

    private function sendPasswordResetEmail(PassphraseResetRequest $request)
    {
        $message = (new \Swift_Message('Poppy Seed Pets: Password Reset Request'))
            ->setFrom('help+resetpassword@poppyseedpets.com')
            ->setTo($request->getUser()->getEmail())
            ->setBody(
                'Ah! You lost your password? Sorry! That sucks! Let\'s get that fixed up!' . "\n\n" .
                'To reset your password, use this link:' . "\n\n" .
                "\t" . 'https://poppyseedpets.com/'
            )
        ;

        $this->mailer->send($message);
    }
}