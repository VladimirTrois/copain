<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'USER_UNIQUE_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:collection', 'user:read'])]
    private ?int $id;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(groups: ['create', 'update'])]
    #[Assert\Email(groups: ['create', 'update'])]
    #[Groups(['user:collection', 'user:read', 'user:write'])]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    #[Groups(['user:collection', 'user:read', 'user:write'])]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column(nullable: true)]
    private ?string $password = null;

    /**
     * Plain password, not stored in DB.
     */
    #[Assert\Length(min: 6)]
    #[Groups(['user:write'])]
    #[SerializedName('password')]
    private ?string $plainPassword = null;

    /**
     * @var Collection<int, BusinessUser>
     */
    #[ORM\OneToMany(targetEntity: BusinessUser::class, mappedBy: 'user', orphanRemoval: true, cascade: ['persist'])]
    #[Groups(['user:read', 'user:write'])]
    private Collection $businesses;

    public function __construct()
    {
        $this->businesses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function hasPassword(): bool
    {
        return $this->password !== null;
    }

    /**
     * Return the plain password (not persisted).
     */
    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    /**
     * Set the plain password (not persisted).
     */
    public function setPlainPassword(?string $plainPassword): static
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        $this->plainPassword = null;
    }

    /**
     * @return Collection<int, BusinessUser>
     */
    public function getBusinesses(): Collection
    {
        return $this->businesses;
    }

    public function addBusiness(BusinessUser $business): static
    {
        if (! $this->businesses->contains($business)) {
            $this->businesses->add($business);
            $business->setUser($this);
        }

        return $this;
    }

    public function removeBusiness(BusinessUser $business): static
    {
        if ($this->businesses->removeElement($business)) {
            // set the owning side to null (unless already changed)
            if ($business->getUser() === $this) {
                $business->setUser(null);
            }
        }

        return $this;
    }
}
