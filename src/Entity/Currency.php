<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\CurrencyRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
    ],
    normalizationContext: ['groups' => ['currency:read']]
)]
#[ORM\Entity(repositoryClass: CurrencyRepository::class)]
class Currency
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['currency:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['currency:read', 'user:read', 'wish:read', 'country:read'])]
    private ?string $code = null;

    #[ORM\Column(length: 255)]
    #[Groups(['currency:read', 'user:read', 'wish:read', 'country:read'])]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Groups(['currency:read', 'user:read', 'wish:read', 'country:read'])]
    private ?string $symbol = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 6)]
    #[Groups(['currency:read', 'user:read', 'wish:read', 'country:read'])]
    private ?string $exchangeRate = null;

    #[ORM\Column]
    #[Groups(['currency:read', 'user:read', 'wish:read', 'country:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getSymbol(): ?string
    {
        return $this->symbol;
    }

    public function setSymbol(string $symbol): static
    {
        $this->symbol = $symbol;

        return $this;
    }

    public function getExchangeRate(): ?string
    {
        return $this->exchangeRate;
    }

    public function setExchangeRate(string $exchangeRate): static
    {
        $this->exchangeRate = $exchangeRate;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
