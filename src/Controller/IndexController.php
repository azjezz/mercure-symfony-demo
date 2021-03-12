<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ChatRoomRepository;
use App\Security\Security;
use App\Service\Responder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Error\Error as TwigError;

final class IndexController
{
    public function __construct(
        private Responder $responder,
        private Security $security,
        private ChatRoomRepository $repository,
    ) {
    }

    /**
     * @throws TwigError
     */
    #[Route("/", name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        if (!$this->security->isAuthenticated()) {
            return $this->responder->route('user_register');
        }

        $rooms = $this->repository->findAll();

        return $this->responder->render('index.html.twig', [
            'rooms' => $rooms,
        ]);
    }
}
