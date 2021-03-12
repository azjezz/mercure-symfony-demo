<?php

declare(strict_types=1);

namespace App\Tests\Controller\User;

use App\Fixtures\UserFixture;
use App\Test\TestCase;

final class LogoutControllerTest extends TestCase
{
    protected array

    $fixtures = [
        UserFixture::class,
    ];

    public function testLogout(): void
    {
        $crawler = $this->browser->request('GET', '/user/login');

        self::assertResponseIsSuccessful();
        self::assertRouteSame('user_login');

        $title = $crawler->filter('h3.uk-card-title')->first();
        self::assertSame('Login', $title->text());

        $loginButton = $crawler->selectButton('Continue');
        $loginForm = $loginButton->form();

        $loginForm->setValues([
            'username' => UserFixture::USER['username'],
            'password' => UserFixture::USER['password'],
        ]);

        $this->browser->submit($loginForm);

        self::assertResponseRedirects('/');
        $crawler = $this->browser->followRedirect();
        $username = $crawler->filter('.uk-navbar-right > ul:nth-child(1) > li:nth-child(1) > a:nth-child(1)');

        self::assertSame(UserFixture::USER['username'], $username->text());

        $this->browser->request('GET', '/user/logout');

        self::assertResponseRedirects();
        $crawler = $this->browser->followRedirect();
        $loginLink = $crawler->filter('.uk-navbar-right > ul:nth-child(1) > li:nth-child(2) > a:nth-child(1)');

        self::assertSame('Login', $loginLink->text());
    }
}
