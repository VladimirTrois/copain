<?php

namespace App\Entity;

use App\Entity\Traits\SoftDeleteable;
use App\Entity\Traits\Timestampable;
use App\Repository\BusinessRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity(repositoryClass: BusinessRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_NAME', fields: ['name'])]
class Business
{
    use Timestampable;
    use SoftDeleteable;

    #[ORM\Id]
    #[ORM\Column(type: UlidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.ulid_generator')]
    private ?Ulid $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Groups(['business:read', 'business:write'])]
    private ?string $name = null;

    public function getId(): ?Ulid
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
