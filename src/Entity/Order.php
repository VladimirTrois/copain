<?php

namespace App\Entity;

use App\Entity\Traits\SoftDeleteable;
use App\Entity\Traits\Timestampable;
use App\Exception\BusinessLogicException;
use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: '`order`')]
class Order
{
    use Timestampable;
    use SoftDeleteable;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    private Customer $customer;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    private Business $business;

    #[ORM\Column]
    private ?\DateTime $pickUpDate = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isValidatedByCustomer = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isValidatedByBusiness = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isPickedUp = null;

    /**
     * @var Collection<int, OrderItem>
     */
    #[ORM\OneToMany(targetEntity: OrderItem::class, mappedBy: 'order', orphanRemoval: true, cascade: [
        'persist',
        'remove',
    ])]
    private Collection $orderItems;

    public function __construct()
    {
        $this->orderItems = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCustomer(): Customer
    {
        return $this->customer;
    }

    public function setCustomer(Customer $customer): static
    {
        $this->customer = $customer;

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

    public function getPickUpDate(): ?\DateTime
    {
        return $this->pickUpDate;
    }

    public function setPickUpDate(\DateTime $pickUpDate): static
    {
        $this->pickUpDate = $pickUpDate;

        return $this;
    }

    public function isValidatedByCustomer(): ?bool
    {
        return $this->isValidatedByCustomer;
    }

    public function setIsValidatedByCustomer(?bool $isValidatedByCustomer): static
    {
        $this->isValidatedByCustomer = $isValidatedByCustomer;

        return $this;
    }

    public function isValidatedByBusiness(): ?bool
    {
        return $this->isValidatedByBusiness;
    }

    public function setIsValidatedByBusiness(?bool $isValidatedByBusiness): static
    {
        $this->isValidatedByBusiness = $isValidatedByBusiness;

        return $this;
    }

    public function isPickedUp(): ?bool
    {
        return $this->isPickedUp;
    }

    public function setIsPickedUp(?bool $isPickedUp): static
    {
        $this->isPickedUp = $isPickedUp;

        return $this;
    }

    /**
     * @return Collection<int, OrderItem>
     */
    public function getOrderItems(): Collection
    {
        return $this->orderItems;
    }

    public function addOrderItem(OrderItem $orderItem): static
    {
        if (! $this->business->isArticleFromBusiness($orderItem->getArticle())) {
            throw new BusinessLogicException('An article is not from the business.');
        }
        if (! $this->orderItems->contains($orderItem)) {
            $this->orderItems->add($orderItem);
            $orderItem->setOrder($this);
        }

        return $this;
    }

    public function removeOrderItem(OrderItem $orderItem): static
    {
        if ($this->orderItems->removeElement($orderItem)) {
            // set the owning side to null (unless already changed)
            if ($orderItem->getOrder() === $this) {
            }
        }

        return $this;
    }
}
