<?php

namespace App\Entity;

use App\Repository\WarehouseRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WarehouseRepository::class)]
class Warehouse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $code_ut = null;

    #[ORM\Column(length: 255)]
    private ?string $adresse_warehouse = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255 , nullable: true)]
    private ?string $nomEntreprise = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCodeUt(): ?string
    {
        return $this->code_ut;
    }

    public function setCodeUt(string $code_ut): static
    {
        $this->code_ut = $code_ut;

        return $this;
    }

    public function getAdresseWarehouse(): ?string
    {
        return $this->adresse_warehouse;
    }

    public function setAdresseWarehouse(string $adresse_warehouse): static
    {
        $this->adresse_warehouse = $adresse_warehouse;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getNomEntreprise(): ?string
    {
        return $this->nomEntreprise;
    }

    public function setNomEntreprise(string $nomEntreprise): static
    {
        $this->nomEntreprise = $nomEntreprise;

        return $this;
    }
}
