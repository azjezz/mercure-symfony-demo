<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Security\Security;
use App\Service\Responder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Twig\Error\Error as TwigError;

#[Route('/user')]
final class LoginController
{
    public function __construct(
        private Responder $responder,
        private Security $security,
        private AuthenticationUtils $authenticationUtils,
    ) {
    }

    /**
     * @throws TwigError
     */
    #[Route('/login', name: 'user_login', methods: ['GET', 'POST'])]
    public function login(): Response
    {
        if ($this->security->isAuthenticated()) {
            return $this->responder->route('index');
        }

        return $this->responder->render('user/login.html.twig', [
            'last_username' => $this->authenticationUtils->getLastUsername(),
            'error' => $this->authenticationUtils->getLastAuthenticationError(),
        ]);
    }
}
