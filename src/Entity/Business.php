<?php

namespace App\Entity;

use App\Entity\Traits\SoftDeleteable;
use App\Entity\Traits\Timestampable;
use App\Repository\BusinessRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BusinessRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_NAME', fields: ['name'])]
class Business
{
    use Timestampable;
    use SoftDeleteable;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['business:list', 'business:read', 'user:read'])]
    private ?int $id;

    #[ORM\Column(length: 255, unique: true)]
    #[Groups(['business:list', 'business:read', 'business:write', 'user:read'])]
    #[Assert\NotNull()]
    private ?string $name = null;

    /**
     * @var Collection<int, BusinessUser>
     */
    #[ORM\OneToMany(targetEntity: BusinessUser::class, mappedBy: 'business')]
    #[Groups(['business:read'])]
    private Collection $businessUsers;

    public function __construct()
    {
        $this->businessUsers = new ArrayCollection();
    }

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

    /**
     * @return Collection<int, BusinessUser>
     */
    public function getBusinessUsers(): Collection
    {
        return $this->businessUsers;
    }

    public function addBusinessUser(BusinessUser $businessUser): static
    {
        if (!$this->businessUsers->contains($businessUser)) {
            $this->businessUsers->add($businessUser);
            $businessUser->setBusiness($this);
        }

        return $this;
    }

    public function removeBusinessUser(BusinessUser $businessUser): static
    {
        if ($this->businessUsers->removeElement($businessUser)) {
            // set the owning side to null (unless already changed)
            if ($businessUser->getBusiness() === $this) {
                $businessUser->setBusiness(null);
            }
        }

        return $this;
    }

    public function isOwnedBy(User $user): bool
    {
        foreach ($this->getBusinessUsers() as $bu) {
            if ($bu->getUser() === $user && in_array('owner', $bu->getResponsibilities(), true)) {
                return true;
            }
        }

        return false;
    }
}
