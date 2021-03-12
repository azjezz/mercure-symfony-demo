<?php

declare(strict_types=1);

namespace App\Service;

use Psl\Type;
use Psr\Link\LinkProviderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\WebLink\GenericLinkProvider;
use Symfony\Component\WebLink\Link;
use Twig\Environment;
use Twig\Error\Error as TwigError;

final class Responder
{
    public function __construct(
        private Environment $twig,
        private UrlGeneratorInterface $urlGenerator,
        private SerializerInterface $serializer,
    ) {
    }

    /**
     * Render the given twig template and return an HTML response.
     *
     * @param array<string, string|list<string>> $headers
     *
     * @throws TwigError
     */
    public function render(string $template, array $context = [], int $status = 200, array $headers = []): Response
    {
        $content = $this->twig->render($template, $context);
        $response = new Response($content, $status, $headers);
        if (!$response->headers->has('Content-Type')) {
            $response->headers->set('Content-Type', 'text/html; charset=UTF-8');
        }

        return $response;
    }

    /**
     * Returns a RedirectResponse to the given URL.
     *
     * @param array<string, string|list<string>> $headers
     */
    public function redirect(string $url, int $status = 302, array $headers = []): RedirectResponse
    {
        return new RedirectResponse($url, $status, $headers);
    }

    /**
     * Returns a RedirectResponse to the given route with the given parameters.
     *
     * @param array<array-key, scalar> $parameters
     * @param array<string, list<string>> $headers
     */
    public function route(
        string $route,
        array $parameters = [],
        int $status = 302,
        array $headers = [],
    ): RedirectResponse {
        $url = $this->urlGenerator->generate($route, $parameters);

        return $this->redirect($url, $status, $headers);
    }

    /**
     * Returns a JsonResponse that uses the serializer component if enabled, or json_encode.
     *
     * @param array<string, string|list<string>> $headers
     * @param array<string, mixed> $context
     */
    public function json(
        mixed $data,
        int $status = 200,
        array $headers = [],
        array $context = [],
    ): JsonResponse {
        $json = $this->serializer->serialize($data, 'json', array_merge([
            'json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS,
        ], $context));

        return new JsonResponse($json, $status, $headers, true);
    }
    
    public function link(Request $request, Link $link): void
    {
        $linkProvider = Type\nullable(Type\object(LinkProviderInterface::class))
            ->assert($request->attributes->get('_links'));
        
        if (null === $linkProvider) {
            $request->attributes->set('_links', new GenericLinkProvider([$link]));

            return;
        }

        $request->attributes->set('_links', $linkProvider->withLink($link));
    }
}
