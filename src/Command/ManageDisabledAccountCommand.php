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
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class ManageDisabledAccountCommand extends Command
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('app:manage-disabled-account')
            ->setDescription('Manage the disabled status of a user account.')
            ->addArgument('identifier', InputArgument::REQUIRED, 'User email address or numeric ID')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $identifier = trim($input->getArgument('identifier'));

        if ($identifier === '') {
            $output->writeln('<error>Identifier may not be blank.</error>');
            return self::FAILURE;
        }

        // Determine lookup method based on identifier format
        $user = $this->findUser($identifier, $output);

        if ($user === null) {
            return self::FAILURE;
        }

        // Check for protected accounts
        if (str_ends_with(strtolower($user->getEmail()), '@poppyseedpets.com')) {
            $output->writeln('<error>Cannot manage accounts with @poppyseedpets.com email addresses.</error>');
            return self::FAILURE;
        }

        if ($user->getIsLocked()) {
            $output->writeln('<error>Cannot manage locked accounts. This account is locked and requires different handling.</error>');
            return self::FAILURE;
        }

        // Display account summary
        $this->displayAccountSummary($user, $output);

        // Present options
        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion(
            'What would you like to do?',
            [
                '1' => 'Disable the account',
                '2' => 'Undisable the account',
                '3' => 'Make no change',
            ],
            '3'
        );
        $question->setErrorMessage('Invalid choice %s.');

        $choice = $helper->ask($input, $output, $question);

        switch ($choice) {
            case '1':
                $user->setDisabledOn(new \DateTimeImmutable());
                $this->em->flush();
                $output->writeln('<info>Account has been disabled.</info>');
                break;
            case '2':
                $user->setDisabledOn(null);
                $this->em->flush();
                $output->writeln('<info>Account has been undisabled.</info>');
                break;
            case '3':
            default:
                $output->writeln('No changes made.');
                break;
        }

        return self::SUCCESS;
    }

    private function findUser(string $identifier, OutputInterface $output): ?User
    {
        // If contains @, search by email
        if (str_contains($identifier, '@')) {
            $user = $this->em->getRepository(User::class)->findOneBy(['email' => $identifier]);

            if ($user === null) {
                $output->writeln('<error>No user found with email address: ' . $identifier . '</error>');
            }

            return $user;
        }

        // If numeric, search by ID
        if (ctype_digit($identifier)) {
            $user = $this->em->getRepository(User::class)->find((int) $identifier);

            if ($user === null) {
                $output->writeln('<error>No user found with ID: ' . $identifier . '</error>');
            }

            return $user;
        }

        // Neither email nor numeric ID
        $output->writeln('<error>Invalid identifier format. Please provide either:</error>');
        $output->writeln('<error>  - An email address (must contain @)</error>');
        $output->writeln('<error>  - A numeric user ID</error>');

        return null;
    }

    private function displayAccountSummary(User $user, OutputInterface $output): void
    {
        $output->writeln('');
        $output->writeln('<info>Account Summary</info>');
        $output->writeln('===============');
        $output->writeln('ID:         ' . $user->getId());
        $output->writeln('Name:       ' . $user->getName());
        $output->writeln('Email:      ' . $user->getEmail());
        $output->writeln('Registered: ' . $user->getRegisteredOn()->format('Y-m-d H:i:s'));

        if ($user->isDisabled()) {
            $output->writeln('Status:     <comment>DISABLED</comment> (since ' . $user->getDisabledOn()->format('Y-m-d H:i:s') . ')');
        } else {
            $output->writeln('Status:     <info>Active</info>');
        }

        $output->writeln('');
    }
}
