<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Entité représentant une commande client
 */
#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
class Order
{
    /**
     * Identifiant unique de la commande
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Statut de la commande (ex: pending, paid, shipped, delivered)
     */
    #[ORM\Column(length: 32)]
    private ?string $status = null;

    /**
     * Montant total de la commande
     */
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $total = null;

    /**
     * Date et heure à laquelle la commande a été passée
     */
    #[ORM\Column]
    private ?\DateTime $dateat = null;

    /**
     * Utilisateur ayant passé la commande
     */
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

<<<<<<< HEAD
    #[ORM\OneToMany(mappedBy: 'orderRef', targetEntity: OrderItem::class, orphanRemoval: true)]
    private $orderItems;

    public function __construct()
    {
        $this->orderItems = new \Doctrine\Common\Collections\ArrayCollection();
    }

=======
    /**
     * @var Collection<int, OrderItem>
     */
    #[ORM\OneToMany(targetEntity: OrderItem::class, mappedBy: 'orderRef', orphanRemoval: true)]
    private Collection $items;

    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    /**
     * Récupère l'identifiant unique de la commande
     * 
     * @return int|null L'ID de la commande
     */
>>>>>>> 6faf1b0eed04b37f0acd80b72ee2fc49a91ceeb7
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Récupère le statut actuel de la commande
     * 
     * @return string|null Le statut de la commande
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * Définit le statut de la commande
     * 
     * @param string $status Le nouveau statut
     * @return static
     */
    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Récupère le montant total de la commande
     * 
     * @return string|null Le montant total
     */
    public function getTotal(): ?string
    {
        return $this->total;
    }

    /**
     * Définit le montant total de la commande
     * 
     * @param string $total Le montant total
     * @return static
     */
    public function setTotal(string $total): static
    {
        $this->total = $total;

        return $this;
    }

    /**
     * Récupère la date de création de la commande
     * 
     * @return \DateTime|null La date de la commande
     */
    public function getDateat(): ?\DateTime
    {
        return $this->dateat;
    }

    /**
     * Définit la date de création de la commande
     * 
     * @param \DateTime $dateat La nouvelle date
     * @return static
     */
    public function setDateat(\DateTime $dateat): static
    {
        $this->dateat = $dateat;

        return $this;
    }

    /**
     * Récupère l'utilisateur associé à la commande
     * 
     * @return User|null L'entité User
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * Définit l'utilisateur associé à la commande
     * 
     * @param User|null $user L'entité User
     * @return static
     */
    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
<<<<<<< HEAD
     * @return \Doctrine\Common\Collections\Collection<int, OrderItem>
     */
    public function getOrderItems(): \Doctrine\Common\Collections\Collection
    {
        return $this->orderItems;
    }

    public function addOrderItem(OrderItem $orderItem): static
    {
        if (!$this->orderItems->contains($orderItem)) {
            $this->orderItems->add($orderItem);
            $orderItem->setOrderRef($this);
=======
     * @return Collection<int, OrderItem>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(OrderItem $item): static
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setOrderRef($this);
>>>>>>> 6faf1b0eed04b37f0acd80b72ee2fc49a91ceeb7
        }

        return $this;
    }

<<<<<<< HEAD
    public function removeOrderItem(OrderItem $orderItem): static
    {
        if ($this->orderItems->removeElement($orderItem)) {
            // set the owning side to null (unless already changed)
            if ($orderItem->getOrderRef() === $this) {
                $orderItem->setOrderRef(null);
=======
    public function removeItem(OrderItem $item): static
    {
        if ($this->items->removeElement($item)) {
            // set the owning side to null (unless already changed)
            if ($item->getOrderRef() === $this) {
                $item->setOrderRef(null);
>>>>>>> 6faf1b0eed04b37f0acd80b72ee2fc49a91ceeb7
            }
        }

        return $this;
    }
}
