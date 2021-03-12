<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Entity\User;
use App\Security\Security;
use App\Service\Registration;
use App\Service\Responder;
use Doctrine\ORM\ORMException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Error\Error as TwigError;

#[Route('/user')]
final class RegisterController
{
    public function __construct(
        private Responder $responder,
        private Registration $registration,
        private Security $security,
    ) {
    }

    /**
     * @throws ORMException
     * @throws TwigError
     */
    #[Route('/register', name: 'user_register', methods: ['GET', 'POST'])]
    public function register(Request $request): Response
    {
        if ($this->security->isAuthenticated()) {
            return $this->responder->route('index');
        }

        $user = new User();
        $form = $this->registration->createForm($user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->registration->register($user);

            return $this->security->authenticate($user, $request);
        }

        return $this->responder->render('user/register.html.twig', [
            'form' => $form->createView(),
            'errors' => $form->getErrors(true),
        ]);
    }
}
