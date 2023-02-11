<?php
namespace App\Service;

use App\Entity\PassphraseResetRequest;
use App\Entity\User;
use App\Functions\PlayerLogHelpers;
use App\Functions\StringFunctions;
use Doctrine\ORM\EntityManagerInterface;

class PassphraseResetService
{
    private \Swift_Mailer $mailer;
    private EntityManagerInterface $em;

    public function __construct(\Swift_Mailer $mailer, EntityManagerInterface $em)
    {
        $this->mailer = $mailer;
        $this->em = $em;
    }

    public function requestReset(User $user): bool
    {
        $now = new \DateTimeImmutable();

        PlayerLogHelpers::Create(
            $this->em,
            $user,
            'A passphrase reset request was made for `' . $user->getEmail() . '`.',
            [ 'Account & Security' ]
        );

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
        $message = (new \Swift_Message('✿ Poppy Seed Pets: Password Reset Request'))
            ->setFrom('help+resetpassword@poppyseedpets.com')
            ->setTo($request->getUser()->getEmail())
            ->setBody(
                'Ah! You lost your password? Sorry! Let\'s get that fixed up!' . "\n\n" .
                'To reset your password, use this link:' . "\n\n" .
                'https://poppyseedpets.com/resetPassphrase/' . $request->getCode() . "\n"
            )
        ;

        $this->mailer->send($message);
    }
}
