<?php

namespace Application\Command;

use Application\Model\UsersTable;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Sets a new password for a user, looked up by login or email. The password
 * is prompted for without echo so it stays out of shell history.
 */
class ResetPassword extends Command
{
    public function __construct(private readonly UsersTable $usersTable)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Set a new password for a user');
        $this->addArgument('user', InputArgument::REQUIRED, 'Login or email of the user');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $user = trim((string) $input->getArgument('user'));

        $matches = iterator_to_array($this->usersTable->select(function ($select) use ($user) {
            $select->columns(['id', 'login', 'email'])
                ->where->equalTo('login', $user)->or->equalTo('email', $user);
        }));

        if (!$matches) {
            $io->error("No user with login or email '$user'.");
            return Command::FAILURE;
        }
        if (count($matches) > 1) {
            $io->error("More than one user matches '$user'. Use the login instead.");
            return Command::FAILURE;
        }
        $match = $matches[0];

        $password = $this->askPassword($io, 'New password');
        // passwordHash() returns null for anything empty() calls empty, which
        // includes "0", and writing that null would leave the account with no
        // password at all.
        $hash = $this->usersTable->passwordHash($password);
        if ($hash === null) {
            $io->error('Password cannot be empty.');
            return Command::FAILURE;
        }
        if ($password !== $this->askPassword($io, 'Confirm password')) {
            $io->error('Passwords do not match.');
            return Command::FAILURE;
        }

        $this->usersTable->update(['password' => $hash], ['id' => $match['id']]);

        $io->success("Password updated for {$match['login']}.");
        return Command::SUCCESS;
    }

    /**
     * Symfony falls back to a visible prompt when it cannot hide input, which
     * would print the password on screen. Refuse instead.
     */
    private function askPassword(SymfonyStyle $io, string $prompt): string
    {
        $question = (new Question($prompt))->setHidden(true)->setHiddenFallback(false);
        return (string) $io->askQuestion($question);
    }
}
