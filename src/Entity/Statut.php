<?php

namespace App\Entity;

use App\Repository\StatutRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StatutRepository::class)]
class Statut
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $id_status = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Colis $colis = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date_status = null;

    #[ORM\Column(length: 255)]
    private ?string $localisation = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdStatus(): ?int
    {
        return $this->id_status;
    }

    public function setIdStatus(int $id_status): static
    {
        $this->id_status = $id_status;

        return $this;
    }

    public function getColis(): ?Colis
    {
        return $this->colis;
    }

    public function setColis(?Colis $colis): static
    {
        $this->colis = $colis;

        return $this;
    }

    public function getDateStatus(): ?\DateTimeInterface
    {
        return $this->date_status;
    }

    public function setDateStatus(\DateTimeInterface $date_status): static
    {
        $this->date_status = $date_status;

        return $this;
    }

    public function getLocalisation(): ?string
    {
        return $this->localisation;
    }

    public function setLocalisation(string $localisation): static
    {
        $this->localisation = $localisation;

        return $this;
    }
}
