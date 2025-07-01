<?php

namespace App\Entity;

use App\Repository\BusinessUserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: BusinessUserRepository::class)]
#[ORM\Table(name: 'business_user')]
#[ORM\UniqueConstraint(name: 'UNIQ_USER_BUSINESS', columns: ['user_id', 'business_id'])]
class BusinessUser
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:read'])]
    private ?int $id;

    #[ORM\ManyToOne(inversedBy: 'businessUsers')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['user:read', 'user:write'])]
    private ?Business $business = null;

    #[ORM\ManyToOne(inversedBy: 'businesses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    #[Groups(['user:read', 'user:write'])]
    private array $responsibilities = [];

    public function __construct()
    {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBusiness(): ?Business
    {
        return $this->business;
    }

    public function setBusiness(?Business $business): static
    {
        $this->business = $business;

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

    public function getResponsibilities(): array
    {
        return $this->responsibilities;
    }

    /**
     * @param list<string> $responsibilities
     */
    public function setResponsibilities(array $responsibilities): static
    {
        $this->responsibilities = $responsibilities;

        return $this;
    }
}
