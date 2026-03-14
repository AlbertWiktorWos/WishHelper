<?php

namespace App\Service\Twig;

use App\Service\Infrastructure\MercureTokenGenerator;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

#[AutoconfigureTag('twig.extension')]
class MercureExtension extends AbstractExtension
{
    private MercureTokenGenerator $tokenGenerator;

    public function __construct(MercureTokenGenerator $tokenGenerator)
    {
        $this->tokenGenerator = $tokenGenerator;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('mercure_token', [$this, 'generateToken']),
        ];
    }

    public function generateToken(string $topic, int $ttl = 3600): string
    {
        return $this->tokenGenerator->generate($topic, $ttl);
    }
}
