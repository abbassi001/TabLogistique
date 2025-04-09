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
    private ?string $localisation_warehouse = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

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

    public function getLocalisationWarehouse(): ?string
    {
        return $this->localisation_warehouse;
    }

    public function setLocalisationWarehouse(string $localisation_warehouse): static
    {
        $this->localisation_warehouse = $localisation_warehouse;

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
}
