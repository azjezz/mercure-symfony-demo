<?php

declare(strict_types=1);

namespace App\Controller\PasswordReset;

use App\Service\PasswordReset;
use App\Service\Responder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;
use Twig\Error\Error as TwigError;

#[Route('/password-reset')]
final class ConfirmationController
{
    public function __construct(
        private Responder $responder,
        private PasswordReset $passwordReset,
        private ResetPasswordHelperInterface $resetPasswordHelper,
    ) {
    }

    /**
     * @throws TwigError
     */
    #[Route('/confirm', name: 'password_reset_confirm', methods: ['GET'])]
    public function confirm(Request $request): Response
    {
        // We prevent users from directly accessing this page
        if (!$this->passwordReset->canCheckEmail($request->getSession())) {
            return $this->responder->route('password_reset_request');
        }

        return $this->responder->render('password-reset/check-email.html.twig', [
            'tokenLifetime' => $this->resetPasswordHelper->getTokenLifetime(),
        ]);
    }
}
