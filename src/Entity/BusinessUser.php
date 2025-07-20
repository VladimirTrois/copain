<?php

namespace App\Entity;

use App\Enum\Responsibility;
use App\Repository\BusinessUserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

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
    private Business $business;

    #[ORM\ManyToOne(inversedBy: 'businesses')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['business:read'])]
    private User $user;

    /**
     * @var string[] List of responsibility values as strings
     */
    #[ORM\Column(type: 'json')]
    #[Groups(['user:read', 'user:write', 'business:read'])]
    #[Assert\Choice(callback: [Responsibility::class, 'cases'], message: 'Invalid responsibility.')]
    private array $responsibilities = [];

    public function __construct()
    {
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Responsibility[]
     */
    public function getResponsibilities(): array
    {
        return array_map(fn ($value): Responsibility => Responsibility::from((string) $value), $this->responsibilities);
    }

    /**
     * @param Responsibility[]|string[] $responsibilities
     */
    public function setResponsibilities(array $responsibilities): self
    {
        $this->responsibilities = array_map(
            fn ($r) => $r instanceof Responsibility ? $r->value : Responsibility::from($r)->value,
            $responsibilities
        );

        return $this;
    }
}
