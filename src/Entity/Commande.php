<?php

namespace App\Entity;

use App\Repository\CommandeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommandeRepository::class)]
class Commande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $commandeId = null;

    #[ORM\Column(nullable: true)]
    private ?float $total = null;

    #[ORM\Column(type: Types::OBJECT, nullable: true)]
    private ?object $chambre = null;

    #[ORM\Column(type: Types::OBJECT, nullable: true)]
    private ?object $user = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $startDate = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $endDate = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $create_at = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommandeId(): ?string
    {
        return $this->commandeId;
    }

    public function setCommandeId(?string $commandeId): static
    {
        $this->commandeId = $commandeId;

        return $this;
    }

    public function getTotal(): ?float
    {
        return $this->total;
    }

    public function setTotal(?float $total): static
    {
        $this->total = $total;

        return $this;
    }

    public function getChambre(): ?object
    {
        return $this->chambre;
    }

    public function setChambre(?object $chambre): static
    {
        $this->chambre = $chambre;

        return $this;
    }

    public function getUser(): ?object
    {
        return $this->user;
    }

    public function setUser(?object $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getStartDate(): ?string
    {
        return $this->startDate;
    }

    public function setStartDate(?string $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?string
    {
        return $this->endDate;
    }

    public function setEndDate(?string $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getCreateAt(): ?\DateTimeImmutable
    {
        return $this->create_at;
    }

    public function setCreateAt(\DateTimeImmutable $create_at): static
    {
        $this->create_at = $create_at;

        return $this;
    }
}
