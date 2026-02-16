<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\TagRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert; // assertions

#[ApiResource(
    normalizationContext: ['groups' => ['tag:read']],
    denormalizationContext: ['groups' => ['tag:write']]
)]
#[ORM\Entity(repositoryClass: TagRepository::class)]
#[ApiResource]
class Tag
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Groups(['tag:read', 'wish:read', 'user:read', 'write:read', 'wish:write'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    private ?string $name = null;

    public function getId(): ?int
    {
        return $this->id;
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
}
