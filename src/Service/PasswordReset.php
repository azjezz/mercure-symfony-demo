<?php

declare(strict_types=1);

namespace App\Service;

use Psl\Type;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\PasswordManager;
use Doctrine\ORM\ORMException;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use SymfonyCasts\Bundle\ResetPassword\Exception\InvalidResetPasswordTokenException;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

final class PasswordReset
{
    public const RESET_PASSWORD_PUBLIC_TOKEN_ID = '_internal/ResetPasswordPublicToken';

    public const RESET_PASSWORD_CHECK_EMAIL_ID = '_internal/ResetPasswordCheckEmail';

    public const RESET_PASSWORD_ERROR = 'reset_password_error';

    public function __construct(
        private ResetPasswordHelperInterface $helper,
        private MailerInterface $mailer,
        private Responder $responder,
        private UserRepository $repository,
        private PasswordManager $passwordManager,
    ) {
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function sendPasswordResetEmail(Session $session, string $address): RedirectResponse
    {
        $user = $this->repository->findOneBy([
            'email' => $address,
        ]);

        $session->set(self::RESET_PASSWORD_CHECK_EMAIL_ID, true);

        if (null === $user || !$user->isPasswordResetEnabled()) {
            return $this->responder->route('password_reset_confirm');
        }

        try {
            $resetToken = $this->helper->generateResetToken($user);
        } catch (ResetPasswordExceptionInterface $exception) {
            $flash = $session->getFlashBag();

            $flash->add(self::RESET_PASSWORD_ERROR, $exception->getReason());

            return $this->responder->route('password_reset_request');
        }

        $email = (new TemplatedEmail())
            ->to(new Address((string) $user->getEmail(), $user->getUsername()))
            ->subject('Password Reset')
            ->htmlTemplate('password-reset/email.html.twig')
            ->context([
                'resetToken' => $resetToken,
                'tokenLifetime' => $this->helper->getTokenLifetime(),
            ]);

        $this->mailer->send($email);

        return $this->responder->route('password_reset_confirm');
    }

    public function canCheckEmail(SessionInterface $session): bool
    {
        return $session->has(self::RESET_PASSWORD_CHECK_EMAIL_ID);
    }

    public function storeTokenInSession(SessionInterface $session, string $token): RedirectResponse
    {
        // We store the token in session and remove it from the URL, to avoid the URL being
        // loaded in a browser and potentially leaking the token to 3rd party JavaScript.
        $session->set(self::RESET_PASSWORD_PUBLIC_TOKEN_ID, $token);

        return $this->responder->route('password_reset');
    }

    public function getTokenFromSession(SessionInterface $session): string
    {
        $token = Type\nullable(Type\string())->assert($session->get(self::RESET_PASSWORD_PUBLIC_TOKEN_ID));

        if (null === $token) {
            throw new NotFoundHttpException('No reset password token found in the URL or in the session.');
        }

        return $token;
    }

    /**
     * @throws ResetPasswordExceptionInterface
     */
    public function retrieveUser(string $token): User
    {
        $user = Type\object(User::class)->assert($this->helper->validateTokenAndFetchUser($token));

        if (!$user->isPasswordResetEnabled()) {
            throw new InvalidResetPasswordTokenException('User has password reset feature disabled.');
        }

        return $user;
    }

    /**
     * @throws ORMException
     */
    public function resetPassword(Request $request, User $user, string $token): void
    {
        // A password reset token should be used only once, remove it.
        $this->helper->removeResetRequest($token);

        // Encode the plain password, and set it.
        $hash = $this->passwordManager->encodePassword($user, Type\string()->assert($user->getNewPassword()));

        $this->passwordManager->upgradePassword($user, $hash);

        // The session is cleaned up after the password has been changed.
        $this->cleanSessionAfterReset($request->getSession());
    }

    private function cleanSessionAfterReset(SessionInterface $session): void
    {
        $session->remove(self::RESET_PASSWORD_PUBLIC_TOKEN_ID);
        $session->remove(self::RESET_PASSWORD_CHECK_EMAIL_ID);
    }
}
