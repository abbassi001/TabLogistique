<?php

namespace App\Entity;

use App\Repository\ColisRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ColisRepository::class)]
class Colis
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $codeTracking = null;

    #[ORM\Column(length: 255)]
    private ?string $dimensions = null;

    #[ORM\Column]
    private ?float $poids = null;

    #[ORM\Column]
    private ?float $valeur_declaree = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date_creation = null;

    #[ORM\Column(length: 255)]
    private ?string $nature_marchandise = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description_marchandise = null;

    #[ORM\Column(length: 255)]
    private ?string $classification_douaniere = null;

    #[ORM\ManyToOne(inversedBy: 'destinaire')]
    private ?Expediteur $expediteur = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Destinataire $destinaire = null;

    #[ORM\ManyToOne]
    private ?Warehouse $warehouse = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCodeTracking(): ?string
    {
        return $this->codeTracking;
    }

    public function setCodeTracking(string $codeTracking): static
    {
        $this->codeTracking = $codeTracking;

        return $this;
    }

    public function getDimensions(): ?string
    {
        return $this->dimensions;
    }

    public function setDimensions(string $dimensions): static
    {
        $this->dimensions = $dimensions;

        return $this;
    }

    public function getPoids(): ?float
    {
        return $this->poids;
    }

    public function setPoids(float $poids): static
    {
        $this->poids = $poids;

        return $this;
    }

    public function getValeurDeclaree(): ?float
    {
        return $this->valeur_declaree;
    }

    public function setValeurDeclaree(float $valeur_declaree): static
    {
        $this->valeur_declaree = $valeur_declaree;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }

    public function setDateCreation(\DateTimeInterface $date_creation): static
    {
        $this->date_creation = $date_creation;

        return $this;
    }

    public function getNatureMarchandise(): ?string
    {
        return $this->nature_marchandise;
    }

    public function setNatureMarchandise(string $nature_marchandise): static
    {
        $this->nature_marchandise = $nature_marchandise;

        return $this;
    }

    public function getDescriptionMarchandise(): ?string
    {
        return $this->description_marchandise;
    }

    public function setDescriptionMarchandise(?string $description_marchandise): static
    {
        $this->description_marchandise = $description_marchandise;

        return $this;
    }

    public function getClassificationDouaniere(): ?string
    {
        return $this->classification_douaniere;
    }

    public function setClassificationDouaniere(string $classification_douaniere): static
    {
        $this->classification_douaniere = $classification_douaniere;

        return $this;
    }

    public function getExpediteur(): ?Expediteur
    {
        return $this->expediteur;
    }

    public function setExpediteur(?Expediteur $expediteur): static
    {
        $this->expediteur = $expediteur;

        return $this;
    }

    public function getDestinaire(): ?Destinataire
    {
        return $this->destinaire;
    }

    public function setDestinaire(?Destinataire $destinaire): static
    {
        $this->destinaire = $destinaire;

        return $this;
    }

    public function getWarehouse(): ?Warehouse
    {
        return $this->warehouse;
    }

    public function setWarehouse(?Warehouse $warehouse): static
    {
        $this->warehouse = $warehouse;

        return $this;
    }
}
