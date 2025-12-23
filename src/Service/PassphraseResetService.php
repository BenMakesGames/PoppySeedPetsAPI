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

namespace App\Service;

use App\Entity\PassphraseResetRequest;
use App\Entity\User;
use App\Functions\PlayerLogFactory;
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
            $passwordResetRequest = new PassphraseResetRequest(
                user: $user,
                code: CryptographicFunctions::generateSecureRandomString(40),
                expiresOn: $now->modify('+8 hours')
            );

            $this->em->persist($passwordResetRequest);
            $this->em->flush();

            $this->sendPasswordResetEmail($passwordResetRequest);

            return true;
        }
    }

    private function sendPasswordResetEmail(PassphraseResetRequest $request): void
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
