<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\User;
use App\Service\Registration;
use App\Test\TestCase;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

final class RegistrationTest extends TestCase
{
    public function testRegister(): void
    {
        self::bootKernel();

        $registration = self::$container->get(Registration::class);

        $user = new User();
        $user->setUsername('test');
        $user->setEmail('test@sanyu');
        $user->setPlainPassword('hello');

        $registration->register($user);

        self::assertNull($user->getPlainPassword());
        self::assertNotNull($user->getPassword());

        $encoder = self::$container->get(UserPasswordEncoderInterface::class);
        self::assertTrue($encoder->isPasswordValid($user, 'hello'));
    }
}
