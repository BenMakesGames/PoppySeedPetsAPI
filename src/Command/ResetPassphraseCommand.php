<?php
declare(strict_types=1);

namespace App\Command;

use App\Entity\User;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPNotFoundException;
use App\Functions\StringFunctions;
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
    private IRandom $squirrel3;

    public function __construct(
        EntityManagerInterface $em, UserPasswordHasherInterface $passwordEncoder, IRandom $squirrel3
    )
    {
        $this->em = $em;
        $this->passwordEncoder = $passwordEncoder;
        $this->squirrel3 = $squirrel3;

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
        $terminalCharacters = 'ABCDEFGHJKLMNPQRTUVWXY346789abdeghnq';
        $allCharacters = $terminalCharacters . '%&?-=#';

        $email = trim($input->getArgument('email'));

        if($email === '')
            throw new PSPFormValidationException('E-mail address may not be blank.');

        $user = $this->em->getRepository(User::class)->findOneBy([ 'email' => $email ]);

        if(!$user)
            throw new PSPNotFoundException('There is no user with that e-mail address.');

        $password =
            $terminalCharacters[$this->squirrel3->rngNextInt(0, strlen($terminalCharacters) - 1)] .
            StringFunctions::randomString($allCharacters, $this->squirrel3->rngNextInt(8, 12)) .
            $terminalCharacters[$this->squirrel3->rngNextInt(0, strlen($terminalCharacters) - 1)]
        ;

        $user->setPassword($this->passwordEncoder->hashPassword($user, $password));

        $this->em->flush();

        $output->writeln($user->getName() . '\'s password has been reset:');
        $output->writeln('');
        $output->writeln('   ' . $password);
        $output->writeln('');

        return self::SUCCESS;
    }
}
