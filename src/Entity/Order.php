<?php

namespace App\Entity;

use App\Repository\OrderRepository;
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

    /**
     * Récupère l'identifiant unique de la commande
     * 
     * @return int|null L'ID de la commande
     */
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
}
