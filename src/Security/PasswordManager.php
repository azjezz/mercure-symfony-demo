<?php

declare(strict_types=1);

namespace App\Security;

use Doctrine\ORM\ORMException;
use Psl\Str;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class PasswordManager implements PasswordUpgraderInterface, UserPasswordEncoderInterface
{
    public function __construct(
        private UserRepository $repository,
        private UserPasswordEncoderInterface $encoder
    ) {
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     *
     * @throws ORMException
     */
    public function upgradePassword(UserInterface $user, string $newEncodedPassword, bool $persist = true): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(Str\format('Instances of "%s" are not supported.', get_class($user)));
        }

        $user->setPassword($newEncodedPassword);
        $user->eraseCredentials();

        if ($persist) {
            $this->repository->save($user);
        }
    }

    public function encodePassword(UserInterface $user, string $plainPassword): string
    {
        return $this->encoder->encodePassword($user, $plainPassword);
    }

    public function isPasswordValid(UserInterface $user, string $raw): bool
    {
        return $this->encoder->isPasswordValid($user, $raw);
    }

    public function needsRehash(UserInterface $user): bool
    {
        return $this->encoder->needsRehash($user);
    }
}
