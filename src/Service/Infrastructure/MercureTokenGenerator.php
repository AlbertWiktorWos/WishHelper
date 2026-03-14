<?php

namespace App\Service\Infrastructure;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;

class MercureTokenGenerator
{
    private string $secret;

    public function __construct(string $mercureSecret)
    {
        $this->secret = $mercureSecret;
    }

    public function generate(string $topic, int $ttl = 3600): string
    {
        $config = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::plainText($this->secret)
        );

        $now = new \DateTimeImmutable();
        $iat = $now->getTimestamp();                    // integer
        $exp = $now->getTimestamp() + $ttl;             // integer

        $token = $config->builder()
            ->issuedAt($now->setTimestamp($iat))
            ->expiresAt((new \DateTimeImmutable())->setTimestamp($exp))
            ->withClaim('mercure', [
                'subscribe' => [$topic],
            ])
            ->getToken($config->signer(), $config->signingKey());

        return $token->toString();
    }
}
