<?php

declare(strict_types=1);

namespace App\Command\User;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\ORMException;
use Psl\Arr;
use Psl\Iter;
use Psl\Str;
use Psl\Type;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class PromoteCommand extends Command
{
    public function __construct(
        private UserRepository $repository
    ) {
        parent::__construct('user:promote');
    }

    public function configure(): void
    {
        $this->setDescription('promote user to author, moderator, or admin role.')
            ->addArgument(
                'username',
                InputArgument::REQUIRED,
                'unique username of the user you wish to promote.'
            )
            ->addOption(
                'moderator',
                null,
                InputOption::VALUE_NONE,
                'if this option is specified, the user will be promoted to moderator role.'
            )
            ->addOption(
                'admin',
                null,
                InputOption::VALUE_NONE,
                'if this option is specified, the user will be promoted to admin role.'
            );
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

        $admin = Type\bool()->coerce($input->getOption('admin'));
        $moderator = Type\bool()->coerce($input->getOption('moderator'));

        $roles = [User::ROLE_USER];

        if ($admin) {
            $roles[] = User::ROLE_ADMIN;
        }

        if ($moderator) {
            $roles[] = User::ROLE_MODERATOR;
        }

        /**
         * Make sure that the user doesn't already have these roles.
         */
        if (Iter\reduce($roles, fn (bool $promoted, string $role): bool => $promoted && $user->hasRole($role), true)) {
            $io->warning(Str\format(
                'User "%s" already has the following roles: %s.',
                $username,
                Str\join($roles, ', '),
            ));

            return 1;
        }

        /**
         * Ask for confirmation before promoting to admin role.
         */
        if (Arr\contains($roles, User::ROLE_ADMIN)) {
            $confirmation = $io->confirm(Str\format(
                'Are you sure you want to grant "%s" role to "%s"?',
                User::ROLE_ADMIN,
                $user->getUsername(),
            ));

            if (!$confirmation) {
                return 1;
            }
        }

        if (Arr\contains($roles, User::ROLE_ADMIN) && Arr\count($roles) !== 2) {
            $io->warning('Admins cannot have any additional roles.');

            return 1;
        }

        $user->setRoles($roles);
        $this->repository->save($user);

        $io->success(Str\format(
            'User "%s" has been granted the following roles: %s.',
            $username,
            Str\join($roles, ', ')
        ));

        return 0;
    }
}
