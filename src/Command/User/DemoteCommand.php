<?php

declare(strict_types=1);

namespace App\Command\User;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\ORMException;
use Psl\Str;
use Psl\Type;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class DemoteCommand extends Command
{
    public function __construct(
        private UserRepository $repository
    ) {
        parent::__construct('user:demote');
    }

    public function configure(): void
    {
        $this->setDescription('demote a moderator, author, or an admin to regular user role.')
            ->addArgument('username', InputArgument::REQUIRED, 'unique username of the user you wish to demote.');
    }

    /**
     * @throws ORMException
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $username = Type\string()->assert($input->getArgument('username'));
        $user = $this->repository->findOneBy(['username' => $username]);
        if (null === $user) {
            $io->error(Str\format('User "%s" does not exist.', $username));

            return 1;
        }

        /**
         * Ask for confirmation before demoting admins.
         */
        if ($user->hasRole(User::ROLE_ADMIN)) {
            $confirmation = $io->confirm(Str\format(
                'Are you sure you want to demote admin "%s" to a regular user?',
                $user->getUsername()
            ));

            if (!$confirmation) {
                return 1;
            }
        } elseif (!$user->hasRole(User::ROLE_ADMIN, User::ROLE_MODERATOR)) {
            $io->warning(Str\format('"%s" is already a regular user.', $username));

            return 1;
        }

        $user->setRoles([User::ROLE_USER]);
        $this->repository->save($user);

        $io->success(Str\format('"%s" has been demoted to regular user.', $username));

        return 0;
    }
}
