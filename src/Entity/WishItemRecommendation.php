<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\WishItemRecommendationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    normalizationContext: ['groups' => ['recommendation:read']],
    denormalizationContext: ['groups' => ['recommendation:write']],
    order: ['createdAt' => 'DESC'],
    paginationClientItemsPerPage: true,
    security: "is_granted('ROLE_USER')",
)]
#[ORM\UniqueConstraint(name: 'uniq_wish_user', columns: ['wish_item_id', 'user_id'])]
#[ORM\Entity(repositoryClass: WishItemRecommendationRepository::class)]
class WishItemRecommendation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['recommendation:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['recommendation:read'])]
    private ?WishItem $wishItem = null;

    /**
     * its against Second Normal Form but we need to save this information
     * even if the wish is not shared anymore.
     */
    #[ORM\Column(length: 255)]
    #[Groups(['recommendation:read'])]
    private ?string $wishItemTitle = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['recommendation:read'])]
    private ?User $user = null;

    #[ORM\Column(type: Types::SMALLINT)]
    #[Groups(['recommendation:read'])]
    private ?int $score = null;

    #[ORM\Column]
    #[Groups(['recommendation:read', 'recommendation:write'])]
    private bool $isSeen = false;

    #[ORM\Column]
    #[Groups(['recommendation:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['recommendation:read'])]
    private ?\DateTimeImmutable $notifiedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWishItem(): ?WishItem
    {
        return $this->wishItem;
    }

    public function setWishItem(?WishItem $wishItem): static
    {
        $this->wishItem = $wishItem;

        return $this;
    }

    public function getWishItemTitle(): ?string
    {
        return $this->wishItemTitle;
    }

    public function setWishItemTitle(string $wishItemTitle): static
    {
        $this->wishItemTitle = $wishItemTitle;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function setScore(int $score): static
    {
        $this->score = $score;

        return $this;
    }

    public function isSeen(): ?bool
    {
        return $this->isSeen;
    }

    public function setIsSeen(bool $isSeen): static
    {
        $this->isSeen = $isSeen;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getNotifiedAt(): ?\DateTimeImmutable
    {
        return $this->notifiedAt;
    }

    public function setNotifiedAt(?\DateTimeImmutable $notifiedAt): static
    {
        $this->notifiedAt = $notifiedAt;

        return $this;
    }
}
