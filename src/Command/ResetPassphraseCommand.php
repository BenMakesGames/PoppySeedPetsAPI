<?php

namespace App\Command;

use App\Functions\StringFunctions;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ResetPassphraseCommand extends Command
{
    private $em;
    private $userRepository;
    private $passwordEncoder;

    public function __construct(EntityManagerInterface $em, UserRepository $userRepository, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->em = $em;
        $this->userRepository = $userRepository;
        $this->passwordEncoder = $passwordEncoder;

        parent::__construct();
    }

    protected function configure()
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
            throw new \InvalidArgumentException('E-mail address may not be blank.');

        $user = $this->userRepository->findOneBy([ 'email' => $email ]);

        if(!$user)
            throw new \InvalidArgumentException('There is no user with that e-mail address.');

        $password =
            $terminalCharacters[mt_rand(0, strlen($terminalCharacters) - 1)] .
            StringFunctions::randomString($allCharacters, mt_rand(8, 12)) .
            $terminalCharacters[mt_rand(0, strlen($terminalCharacters) - 1)]
        ;

        $user->setPassword($this->passwordEncoder->encodePassword($user, $password));

        $this->em->flush();

        $output->writeln($user->getName() . '\'s password has been reset:');
        $output->writeln('');
        $output->writeln('   ' . $password);
        $output->writeln('');

        return Command::SUCCESS;
    }
}
