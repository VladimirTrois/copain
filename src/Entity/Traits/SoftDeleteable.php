<?php

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

trait SoftDeleteable
{
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $deleted_at = null;

    public function delete(): void
    {
        $this->deleted_at = new \DateTimeImmutable();
    }

    public function isDeleted(): bool
    {
        return null !== $this->deleted_at;
    }

    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->deleted_at;
    }
}
