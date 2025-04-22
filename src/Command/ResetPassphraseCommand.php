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


namespace App\Command;

use App\Entity\User;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPNotFoundException;
use App\Functions\StringFunctions;
use App\Security\CryptographicFunctions;
use App\Service\IRandom;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ResetPassphraseCommand extends Command
{
    private EntityManagerInterface $em;
    private UserPasswordHasherInterface $passwordEncoder;

    public function __construct(
        EntityManagerInterface $em, UserPasswordHasherInterface $passwordEncoder
    )
    {
        $this->em = $em;
        $this->passwordEncoder = $passwordEncoder;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('app:reset-passphrase')
            ->setDescription('Resets a user\'s passphrase, given their e-mail address.')
            ->addArgument('email')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = trim($input->getArgument('email'));

        if($email === '')
            throw new PSPFormValidationException('E-mail address may not be blank.');

        $user = $this->em->getRepository(User::class)->findOneBy([ 'email' => $email ]);

        if(!$user)
            throw new PSPNotFoundException('There is no user with that e-mail address.');

        $password = CryptographicFunctions::generateSecureRandomString(16);

        $user->setPassword($this->passwordEncoder->hashPassword($user, $password));

        $this->em->flush();

        $output->writeln($user->getName() . '\'s password has been reset:');
        $output->writeln('');
        $output->writeln('   ' . $password);
        $output->writeln('');

        return self::SUCCESS;
    }
}
