<?php

declare(strict_types=1);

namespace App\Fixtures;

use App\Entity\User;
use App\Service\Registration;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ObjectManager;
use Psl\Str;

final class UserFixture extends Fixture
{
    public const ADMIN = [
        'username' => 'azjezz',
        'email' => 'azjezz@protonmail.com',
        'password' => self::DEFAULT_PASSWORD,
        'roles' => [User::ROLE_ADMIN]
    ];

    public const MODERATOR = [
        'username' => 'moon',
        'email' => 'moon@example.com',
        'password' => self::DEFAULT_PASSWORD,
        'roles' => [User::ROLE_MODERATOR]
    ];

    public const USER = [
        'username' => 'mars',
        'email' => 'mars@example.com',
        'password' => self::DEFAULT_PASSWORD,
        'roles' => [User::ROLE_USER]
    ];

    public const DEFAULT_PASSWORD = 'Pa$$w0rd!';

    /**
     * @var list<array{username: string, email: string, roles: list<string>, password: string}>
     */
    private const USERS = [
        self::ADMIN,
        self::MODERATOR,
        self::USER
    ];

    public function __construct(
        private Registration $registration
    ) {
    }

    /**
     * @throws ORMException
     */
    public function load(ObjectManager $manager): void
    {
        foreach (self::USERS as $user) {
            $entity = new User();
            $entity->setUsername($user['username']);
            $entity->setEmail($user['email']);
            $entity->setNewPassword($user['password']);
            $entity->setRoles($user['roles']);

            $this->registration->register($entity);
        }

        for ($i = 1; $i <= 5; ++$i) {
            $entity = new User();
            $entity->setUsername(Str\format('user.%d', $i));
            $entity->setEmail(Str\format('user.%d@example.com', $i));
            $entity->setNewPassword(self::DEFAULT_PASSWORD);
            $entity->setRoles([User::ROLE_USER]);

            $this->registration->register($entity);
        }
    }
}
