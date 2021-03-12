<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security as SymfonySecurity;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;

final class Security implements AuthorizationCheckerInterface
{
    public const FIREWALL = 'main';

    public const ACCESS_DENIED_ERROR = SymfonySecurity::ACCESS_DENIED_ERROR;

    public const AUTHENTICATION_ERROR = SymfonySecurity::AUTHENTICATION_ERROR;

    public const LAST_USERNAME = SymfonySecurity::LAST_USERNAME;

    public const MAX_USERNAME_LENGTH = SymfonySecurity::MAX_USERNAME_LENGTH;

    public function __construct(
        private AuthorizationCheckerInterface $authorizationChecker,
        private TokenStorageInterface $tokenStorage,
        private CsrfTokenManagerInterface $csrfTokenManager,
        private GuardAuthenticatorHandler $guardAuthenticatorHandler,
        private UrlGeneratorInterface $urlGenerator,
        private Authenticator $authenticator
    ) {
    }

    /**
     * Returns whether there's an authenticated user.
     */
    public function isAuthenticated(): bool
    {
        return null !== $this->getUser();
    }

    /**
     * Returns the current security token.
     */
    public function getToken(): ?TokenInterface
    {
        return $this->tokenStorage->getToken();
    }

    /**
     * Returns the current authenticated user.
     */
    public function getUser(): ?User
    {
        $token = $this->getToken();

        if (null === $token) {
            return null;
        }

        $user = $token->getUser();

        if ($user instanceof User) {
            return $user;
        }

        return null;
    }

    public function getAuthenticatedUser(): User
    {
        $user = $this->getUser();
        if (null === $user) {
            $this->denyAccess();
        }

        return $user;
    }

    public function denyAccessUnlessCsrfTokenIsValid(string $action, string $token): void
    {
        $csrfToken = new CsrfToken($action, $token);

        if (!$this->csrfTokenManager->isTokenValid($csrfToken)) {
            $this->denyAccess('Invalid CSRF Token.');
        }
    }

    /**
     * Checks if the attributes are granted against the current authentication token
     * and optionally supplied subject.
     */
    public function isGranted($attribute, $subject = null): bool
    {
        return $this->authorizationChecker->isGranted($attribute, $subject);
    }

    /**
     * Throws an exception unless the attribute is granted against the current authentication token
     * and optionally supplied subject.
     *
     * @throws AccessDeniedException
     */
    public function denyAccessUnlessGranted(
        string $attribute,
        ?object $subject = null,
        string $message = 'Access Denied.'
    ): void {
        if (!$this->isGranted($attribute, $subject)) {
            $this->denyAccess($message, [$attribute], $subject);
        }
    }

    /**
     * @return no-return
     */
    public function denyAccess(
        string $message = 'Access Denied.',
        array $attributes = [],
        ?object $subject = null
    ): void {
        $exception = new AccessDeniedException($message);
        $exception->setAttributes($attributes);
        $exception->setSubject($subject);

        throw $exception;
    }

    public function invalidate(SessionInterface $session): void
    {
        if (!$this->isAuthenticated()) {
            return;
        }

        $this->tokenStorage->setToken(/* null */);
        $session->invalidate();
    }

    /**
     * Convenience method for authenticating the user and returning the
     * Response *if any* for success.
     */
    public function authenticate(User $user, Request $request, string $firewall = self::FIREWALL): Response
    {
        return $this->guardAuthenticatorHandler->authenticateUserAndHandleSuccess(
            $user,
            $request,
            $this->authenticator,
            $firewall,
        ) ?? new RedirectResponse($this->urlGenerator->generate('index'));
    }
}
