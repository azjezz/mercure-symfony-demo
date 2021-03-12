<?php

declare(strict_types=1);

namespace App\Security;

use Stringable;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyUser;

interface UserInterface extends SymfonyUser, Stringable, EquatableInterface
{
    public const ROLE_USER = 'ROLE_USER';
    public const ROLE_MODERATOR = 'ROLE_MODERATOR';
    public const ROLE_ADMIN = 'ROLE_ADMIN';

    public const ROLES = [
        self::ROLE_USER,
        self::ROLE_MODERATOR,
        self::ROLE_ADMIN,
    ];

    public const ROLE_HIERARCHY = [
        self::ROLE_MODERATOR => [self::ROLE_USER],

        self::ROLE_ADMIN => [self::ROLE_MODERATOR, 'ROLE_ALLOWED_TO_SWITCH'],
    ];

    public function __serialize(): array;

    public function __unserialize(array $data): void;

    public function __toString(): string;
}
