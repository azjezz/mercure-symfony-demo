<?php

declare(strict_types=1);

namespace App\Mercure;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key;
use Symfony\Component\Mercure\Update;

final class TokenProvider
{
    public function __construct(
        private string $secret,
    ) {
    }

    public function create(array $publish = [], array $subscribe = []): string
    {
        $key = Key\InMemory::plainText($this->secret);
        $configuration = Configuration::forSymmetricSigner(new Sha256(), $key);

        return $configuration->builder()
            ->withClaim('mercure', ['publish' => $publish, 'subscribe' => $subscribe])
            ->getToken($configuration->signer(), $configuration->signingKey())
            ->toString();
    }

    public function __invoke(Update $update): string
    {
        return $this->create($update->getTopics(), ['*']);
    }
}
