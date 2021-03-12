<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use App\Service\Responder;
use Psl\Type;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Guard\PasswordAuthenticatedInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

final class Authenticator extends AbstractFormLoginAuthenticator implements PasswordAuthenticatedInterface
{
    use TargetPathTrait;

    public function __construct(
        private Responder $responder,
        private CsrfTokenManagerInterface $csrfTokenManager,
        private UserPasswordEncoderInterface $passwordEncoder,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function supports(Request $request): bool
    {
        return 'user_login' === $request->attributes->get('_route')
            && $request->isMethod('POST');
    }

    /**
     * @return array{username: string, password: string, _csrf_token: string}
     */
    public function getCredentials(Request $request): array
    {
        try {
            $credentials = self::getCredentialsType()->coerce($request->request->all());
        } catch (Type\Exception\AssertException) {
            throw new BadRequestException('Invalid credentials.');
        }

        $request->getSession()->set(Security::LAST_USERNAME, $credentials['username']);

        return $credentials;
    }

    public function getUser($credentials, UserProviderInterface $userProvider): User
    {
        $credentials = self::getCredentialsType()->assert($credentials);
        $token = new CsrfToken('authenticate', $credentials['_csrf_token']);

        if (!$this->csrfTokenManager->isTokenValid($token)) {
            throw new InvalidCsrfTokenException('Invalid CSRF token.');
        }

        return Type\object(User::class)
            ->assert($userProvider->loadUserByUsername($credentials['username']));
    }

    public function checkCredentials($credentials, UserInterface $user): bool
    {
        return $this->passwordEncoder->isPasswordValid(
            $user,
            self::getCredentialsType()->assert($credentials)['password']
        );
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function getPassword($credentials): ?string
    {
        return self::getCredentialsType()->assert($credentials)['password'];
    }

    public function onAuthenticationSuccess(
        Request $request,
        TokenInterface $token,
        string $providerKey,
    ): RedirectResponse {
        $targetPath = $this->getTargetPath($request->getSession(), $providerKey);

        if (null !== $targetPath) {
            return $this->responder->redirect($targetPath);
        }

        return $this->responder->route('index');
    }

    protected function getLoginUrl(): string
    {
        return $this->urlGenerator->generate('user_login');
    }

    /**
     * @return Type\TypeInterface<array{
     *      username: non-empty-string,
     *      password: non-empty-string,
     *      _csrf_token: non-empty-string
     * }>
     */
    private static function getCredentialsType(): Type\TypeInterface
    {
        return Type\shape([
            'username' => Type\non_empty_string(),
            'password' => Type\non_empty_string(),
            '_csrf_token' => Type\non_empty_string()
        ]);
    }
}
