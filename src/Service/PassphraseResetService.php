<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\PassphraseResetRequest;
use App\Entity\User;
use App\Functions\PlayerLogFactory;
use App\Functions\StringFunctions;
use App\Security\CryptographicFunctions;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class PassphraseResetService
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly EntityManagerInterface $em
    )
    {
    }

    public function requestReset(User $user): bool
    {
        $now = new \DateTimeImmutable();

        PlayerLogFactory::create(
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
                ->setCode(CryptographicFunctions::generateSecureRandomString(40))
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
                ->setCode(CryptographicFunctions::generateSecureRandomString(40))
            ;

            $this->em->persist($passwordResetRequest);
            $this->em->flush();

            $this->sendPasswordResetEmail($passwordResetRequest);

            return true;
        }
    }

    private function sendPasswordResetEmail(PassphraseResetRequest $request)
    {
        $message = (new Email())
            ->from('help+resetpassword@poppyseedpets.com')
            ->to($request->getUser()->getEmail())
            ->subject('✿ Poppy Seed Pets: Passphrase Reset Request')
            ->text(
                'Ah! You lost your passphrase? Sorry! Let\'s get that fixed up!' . "\n\n" .
                'To reset your passphrase, use this link:' . "\n\n" .
                'https://poppyseedpets.com/resetPassphrase/' . $request->getCode() . "\n"
            )
        ;

        $this->mailer->send($message);
    }
}
