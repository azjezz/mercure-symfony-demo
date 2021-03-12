<?php

declare(strict_types=1);

namespace App\Controller\Message;

use App\Entity\Message;
use App\Security\Security;
use Psl\Type;
use Psl\Json;
use App\Repository\ChatRoomRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mercure\PublisherInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/message')]
final class CreateController
{
    public function __construct(
        private Security $security,
        private ChatRoomRepository $rooms,
        private PublisherInterface $publisher,
        private UrlGeneratorInterface $generator
    ) {
    }
    
    #[Route('/{id}/', name: 'message:create', methods: ['POST'])]
    public function create(Request $request, string $id): Response
    {
        $this->security->denyAccessUnlessGranted('ROLE_USER');

        $room = $this->rooms->find($id);
        if (null === $room) {
            throw new NotFoundHttpException();
        }

        $author = $this->security->getAuthenticatedUser();
        $content = Type\string()->coerce($request->request->get('message'));
        $message = Message::create($author, $content);
        $room->addMessage($message);

        $this->rooms->save($room);

        ($this->publisher)(new Update(
            $this->generator->generate('chat-room:show', ['id' => $id]),
            Json\encode([
                'content' => $message->getContent(),
                'author' => $author->getUsername(),
            ]),
            private: true
        ));

        return new Response(status: 201);
    }
}
