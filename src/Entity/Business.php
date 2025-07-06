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

    /**
     * @var Collection<int, Article>
     */
    #[ORM\OneToMany(targetEntity: Article::class, mappedBy: 'business', orphanRemoval: true)]
    private Collection $articles;

    public function __construct()
    {
        $this->businessUsers = new ArrayCollection();
        $this->articles = new ArrayCollection();
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

    /**
     * @return Collection<int, Article>
     */
    public function getArticles(): Collection
    {
        return $this->articles;
    }

    public function addArticle(Article $article): static
    {
        if (!$this->articles->contains($article)) {
            $this->articles->add($article);
            $article->setBusiness($this);
        }

        return $this;
    }

    public function removeArticle(Article $article): static
    {
        if ($this->articles->removeElement($article)) {
            // set the owning side to null (unless already changed)
            if ($article->getBusiness() === $this) {
                $article->setBusiness(null);
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

    public function hasUser(User $user): bool
    {
        return $this->getBusinessUsers()->exists(
            fn ($key, BusinessUser $bu) => $bu->getUser()->getId() === $user->getId()
        );
    }

    public function getResponsibilitiesFor(User $user): array
    {
        foreach ($this->getBusinessUsers() as $bu) {
            if ($bu->getUser()->getId() === $user->getId()) {
                return $bu->getResponsibilities();
            }
        }

        return [];
    }
}
