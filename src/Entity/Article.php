<?php

namespace App\Entity;

use App\Entity\Traits\SoftDeleteable;
use App\Entity\Traits\Timestampable;
use App\Repository\ArticleRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\UniqueConstraint(name: 'ARTICLE_UNIQ_NAME', fields: ['name'])]
class Article
{
    use Timestampable;
    use SoftDeleteable;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['article:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotNull()]
    #[Groups(['article:read', 'article:write'])]
    private ?string $name = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['article:read', 'article:write'])]
    private ?int $price = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['article:read', 'article:write'])]
    private ?int $weight = null;

    #[ORM\Column]
    #[Groups(['article:read', 'article:write'])]
    private ?int $stock = null;

    #[ORM\Column]
    #[Groups(['article:read', 'article:write'])]
    private ?int $rank = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['article:read', 'article:write'])]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['article:read', 'article:write'])]
    private ?string $image = null;

    #[ORM\Column]
    #[Groups(['article:read', 'article:write'])]
    private ?bool $isAvailable = null;

    #[ORM\ManyToOne(inversedBy: 'articles')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['article:read'])]
    private Business $business;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(?int $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(int $stock): static
    {
        $this->stock = $stock;

        return $this;
    }

    public function getRank(): ?int
    {
        return $this->rank;
    }

    public function setRank(int $rank): static
    {
        $this->rank = $rank;

        return $this;
    }

    public function isAvailable(): ?bool
    {
        return $this->isAvailable;
    }

    public function setIsAvailable(bool $isAvailable): static
    {
        $this->isAvailable = $isAvailable;

        return $this;
    }

    public function getBusiness(): Business
    {
        return $this->business;
    }

    public function setBusiness(Business $business): static
    {
        $this->business = $business;

        return $this;
    }

    public function getWeight(): ?int
    {
        return $this->weight;
    }

    public function setWeight(?int $weight): static
    {
        $this->weight = $weight;

        return $this;
    }
}
