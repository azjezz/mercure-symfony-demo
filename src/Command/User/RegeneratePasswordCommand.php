<?php

declare(strict_types=1);

namespace App\Command\User;

use App\Repository\UserRepository;
use App\Security\PasswordManager;
use Doctrine\ORM\ORMException;
use Psl\SecureRandom;
use Psl\Str;
use Psl\Str\Byte;
use Psl\Type;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Regenerate users password.
 *
 * This command should be used with caution, as its actions cannot be undone.
 *
 * This command doesn't take a password as it's input, instead, it automatically generates a random password
 * for the user.
 *
 * The command also offers the ability to change the password length, and the characters set used to generate
 * passwords.
 *
 * Its recommended *not* to change the characters set, and not to use a password length lower than 16 ( 2^4 ).
 *
 * However, the minimum allowed length for a password is 8 ( 2^3 ), and due to security reasons,
 * the maximum allowed length is 4069 ( 2^12 ).
 *
 * The minimum length for a password character set is 16 ( 2^4 ), and the maximum length is 7.2057594e16 ( 2^56 ).
 *
 * After generating a password, this command will hash and upgrade the user password.
 *
 * When the upgrade goes successfully, the command will display the newly generated password.
 *
 * As we don't persist the plain password, the command output will be the only time you are able to see
 * the raw password to save.
 */
final class RegeneratePasswordCommand extends Command
{
    private const LOWERCASE_CHARSET = 'abcdefghijklmnopqrstuvwxyz';
    private const UPPERCASE_CHARSET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    private const NUMERIC_CHARSET = '0123456789';
    private const SYMBOLS_CHARSET = '([+?%.:&#^@,!-])';

    private const DEFAULT_LENGTH = 2 ** 4;

    private const MINIMUM_LENGTH = 2 ** 3;
    private const MAXIMUM_LENGTH = 2 ** 12;

    private const DEFAULT_CHARSET =
        self::LOWERCASE_CHARSET .
        self::UPPERCASE_CHARSET .
        self::NUMERIC_CHARSET .
        self::SYMBOLS_CHARSET;

    private const MINIMUM_CHARSET_LENGTH = 2 ** 4;
    private const MAXIMUM_CHARSET_LENGTH = 2 ** 56;

    public function __construct(
        private UserRepository $repository,
        private PasswordManager $passwordManager,
    ) {
        parent::__construct('user:regenerate-password');
    }

    public function configure(): void
    {
        $this->setDescription('promote user to author, moderator, or admin role.')
            ->addArgument(
                'username',
                InputArgument::REQUIRED,
                'unique username of the user you wish to regenerate their password.',
            )
            ->addOption(
                'length',
                'l',
                InputOption::VALUE_OPTIONAL,
                'The length of the password to generate.',
                self::DEFAULT_LENGTH
            )
            ->addOption(
                'charset',
                'c',
                InputOption::VALUE_OPTIONAL,
                'The characters set to use for password generation.',
                self::DEFAULT_CHARSET
            );
    }

    /**
     * @throws ORMException
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $username = Type\string()->coerce($input->getArgument('username'));
        $user = $this->repository->findOneBy(['username' => $username]);
        if (null === $user) {
            $io->error(Str\format('User "%s" does not exist.', $username));

            return 1;
        }

        $charset = Type\string()->coerce($input->getOption('charset'));
        $charset_length = Byte\length($charset);

        if ($charset_length < self::MINIMUM_CHARSET_LENGTH || $charset_length > self::MAXIMUM_CHARSET_LENGTH) {
            $io->error('Password charset length must be in the [2^4, 2^56] range.');

            return 1;
        }

        $length = Type\int()->coerce($input->getOption('length'));
        if ($length < self::MINIMUM_LENGTH || $length >= self::MAXIMUM_LENGTH) {
            $io->error('Password length must be in the [2^3, 2^12] range.');

            return 1;
        }

        $password = SecureRandom\string($length, $charset);
        $hash = $this->passwordManager->encodePassword($user, $password);

        $this->passwordManager->upgradePassword($user, $hash, persist: true);

        $io->success(Str\format('Password for "%s" has been regenerated successfully.', $username));
        $io->block(Str\format('<fg=#11aa33>%s</>', $password), 'PASSWORD', 'fg=green', escape: false);

        return 0;
    }
}
