<?php

declare(strict_types=1);

namespace App\Controller\PasswordReset;

use App\Form\PasswordReset;
use App\Security\Security;
use App\Service;
use Doctrine\ORM\ORMException;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use Twig\Error\Error as TwigError;

#[Route('/password-reset')]
final class ResetController
{
    public function __construct(
        private Service\Responder $responder,
        private FormFactoryInterface $factory,
        private Service\PasswordReset $passwordReset,
        private Security $security,
    ) {
    }

    /**
     * @throws ORMException
     * @throws TwigError
     */
    #[Route('/reset/{token}', name: 'password_reset', methods: ['POST', 'GET'])]
    public function reset(Request $request, Session $session, ?string $token = null): Response
    {
        if ($token) {
            return $this->passwordReset->storeTokenInSession($request->getSession(), $token);
        }

        $token = $this->passwordReset->getTokenFromSession($request->getSession());

        try {
            $user = $this->passwordReset->retrieveUser($token);
        } catch (ResetPasswordExceptionInterface $e) {
            $flashes = $session->getFlashBag();
            $flashes->add(Service\PasswordReset::RESET_PASSWORD_ERROR, $e->getReason());

            return $this->responder->route('password_reset_request');
        }

        $form = $this->factory->create(PasswordReset\ResetType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->passwordReset->resetPassword($request, $user, $token);

            return $this->security->authenticate($user, $request);
        }

        return $this->responder->render('password-reset/reset.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }
}
