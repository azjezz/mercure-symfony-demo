<?php

declare(strict_types=1);

namespace App\Controller\PasswordReset;

use App\Form\PasswordReset;
use App\Service;
use Psl\Type;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Error\Error as TwigError;

#[Route('/password-reset')]
final class RequestController
{
    public function __construct(
        private Service\Responder $responder,
        private Service\PasswordReset $passwordReset,
        private FormFactoryInterface $factory,
    ) {
    }

    /**
     * @throws TransportExceptionInterface
     * @throws TwigError
     */
    #[Route('/', name: 'password_reset_request', methods: ['GET', 'POST'])]
    public function request(Request $request, Session $session): Response
    {
        $form = $this->factory->create(PasswordReset\RequestType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $address = Type\string()->assert($form->get('email')->getData());

            return $this->passwordReset->sendPasswordResetEmail($session, $address);
        }

        return $this->responder->render('password-reset/request.html.twig', [
            'form' => $form->createView(),
            'errors' => $this->getErrors($session, $form),
        ]);
    }

    /** @return list<string> */
    private function getErrors(Session $session, FormInterface $form): array
    {
        return Type\mutable_vector(Type\string())
            ->coerce($session->getFlashBag()->get(Service\PasswordReset::RESET_PASSWORD_ERROR))
            ->addAll(
                Type\vector(Type\object(FormError::class))->coerce($form->getErrors())
                    ->map(fn(FormError $error): string => $error->getMessage())
            )->toArray();
    }
}
