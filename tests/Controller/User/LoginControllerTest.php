<?php

declare(strict_types=1);

namespace App\Tests\Controller\User;

use App\Fixtures\UserFixture;
use App\Test\TestCase;

final class LoginControllerTest extends TestCase
{
    protected array $fixtures = [
        UserFixture::class,
    ];

    public function testLogin(): void
    {
        $crawler = $this->browser->request('GET', '/user/login');

        self::assertResponseIsSuccessful();
        self::assertRouteSame('user_login');

        $title = $crawler->filter('h3.uk-card-title')->first();
        self::assertSame('Login', $title->text());

        $loginButton = $crawler->selectButton('Continue');
        $loginForm = $loginButton->form();

        $loginForm->setValues([
            'username' => UserFixture::ADMIN['username'],
            'password' => UserFixture::ADMIN['password'],
        ]);

        $this->browser->submit($loginForm);

        self::assertResponseRedirects('/');
        $crawler = $this->browser->followRedirect();
        $username = $crawler->filter('.uk-navbar-right > ul:nth-child(1) > li:nth-child(1) > a:nth-child(1)');

        self::assertSame(UserFixture::ADMIN['username'], $username->text());
    }

    public function testLoginWithUnregisteredUsername(): void
    {
        $crawler = $this->browser->request('GET', '/user/login');
        $loginButton = $crawler->selectButton('Continue');
        $loginForm = $loginButton->form();
        $loginForm->setValues([
            'username' => 'yyyy',
            'password' => 'xxxx',
        ]);

        $this->browser->submit($loginForm);

        self::assertResponseRedirects('/user/login');
        $crawler = $this->browser->followRedirect();
        $username = $crawler->filter('.uk-alert-danger');

        self::assertSame('Username could not be found.', $username->text());
    }

    public function testLoginWithInvalidCredentials(): void
    {
        $crawler = $this->browser->request('GET', '/user/login');
        $loginButton = $crawler->selectButton('Continue');
        $loginForm = $loginButton->form();
        $loginForm->setValues([
            'username' => UserFixture::ADMIN['username'],
            'password' => 'not-actually-' . UserFixture::ADMIN['password'],
        ]);

        $this->browser->submit($loginForm);

        self::assertResponseRedirects('/user/login');
        $crawler = $this->browser->followRedirect();
        $username = $crawler->filter('.uk-alert-danger');

        self::assertSame('Invalid credentials.', $username->text());
    }
}
