<?php

declare(strict_types=1);

namespace App\Controller\ChatRoom;

use App\Mercure\TokenProvider;
use App\Repository\ChatRoomRepository;
use App\Security\Security;
use App\Service\Responder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\WebLink\Link;

#[Route('/chat-room')]
final class DiscoverController
{
    public function __construct(
        private ChatRoomRepository $repository,
        private Responder $responder,
        private TokenProvider $provider,
        private Security $security,
        private ParameterBagInterface $parameters,
        private UrlGeneratorInterface $generator
    ) {
    }

    #[Route('/discover/{id}', name: 'chat-room:discover', methods: ['GET'])]
    public function index(Request $request, string $id): Response
    {
        $this->security->denyAccessUnlessGranted('ROLE_USER');

        $room = $this->repository->find($id);
        if (null === $room) {
            throw new NotFoundHttpException();
        }

        $this->responder->link($request, new Link(
            'mercure',
            $this->parameters->get('mercure.default_hub')
        ));

        $response = $this->responder->json([
            '@id' => $room->getId(),
        ]);

        $response
            ->headers
            ->setCookie(
                Cookie::create('mercureAuthorization')
                    ->withValue($this->provider->create(subscribe: [
                        $this->generator->generate('chat-room:show', ['id' => $id])
                    ]))
                    ->withPath('/.well-known/mercure')
                    ->withSecure(true)
                    ->withHttpOnly(true)
                    ->withSameSite('strict')
            );

        return $response;
    }
}
