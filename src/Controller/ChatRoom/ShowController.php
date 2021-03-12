<?php

declare(strict_types=1);

namespace App\Controller\ChatRoom;

use App\Repository\ChatRoomRepository;
use App\Security\Security;
use App\Service\Responder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/chat-room')]
final class ShowController
{
    public function __construct(
        private ChatRoomRepository $repository,
        private Responder $responder,
        private Security $security,
    ) {
    }

    #[Route('/{id}', name: 'chat-room:show', methods: ['GET'])]
    public function index(string $id): Response
    {
        $this->security->denyAccessUnlessGranted('ROLE_USER');

        $room = $this->repository->find($id);
        if (null === $room) {
            throw new NotFoundHttpException();
        }

        return $this->responder->render('room.html.twig', [
            'room' => $room,
        ]);
    }
}
