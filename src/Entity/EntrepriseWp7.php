<?php

namespace App\Entity;

use App\Repository\EntrepriseWp7Repository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EntrepriseWp7Repository::class)]
class EntrepriseWp7
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'wp7')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Entreprise $entreprise = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $responsible = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $amountTrigger = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $typeInvestment = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $sourceFinancing = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $amountObtained = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $servicePrice = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $priceInvoiced = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $finishDate = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEntreprise(): ?Entreprise
    {
        return $this->entreprise;
    }

    public function setEntreprise(?Entreprise $entreprise): static
    {
        $this->entreprise = $entreprise;
        return $this;
    }

    public function getResponsible(): ?string
    {
        return $this->responsible;
    }

    public function setResponsible(?string $responsible): static
    {
        $this->responsible = $responsible;
        return $this;
    }

    public function getAmountTrigger(): ?string
    {
        return $this->amountTrigger;
    }

    public function setAmountTrigger(?string $amountTrigger): static
    {
        $this->amountTrigger = $amountTrigger;
        return $this;
    }

    public function getTypeInvestment(): ?string
    {
        return $this->typeInvestment;
    }

    public function setTypeInvestment(?string $typeInvestment): static
    {
        $this->typeInvestment = $typeInvestment;
        return $this;
    }

    public function getSourceFinancing(): ?string
    {
        return $this->sourceFinancing;
    }

    public function setSourceFinancing(?string $sourceFinancing): static
    {
        $this->sourceFinancing = $sourceFinancing;
        return $this;
    }

    public function getAmountObtained(): ?string
    {
        return $this->amountObtained;
    }

    public function setAmountObtained(?string $amountObtained): static
    {
        $this->amountObtained = $amountObtained;
        return $this;
    }

    public function getServicePrice(): ?string
    {
        return $this->servicePrice;
    }

    public function setServicePrice(?string $servicePrice): static
    {
        $this->servicePrice = $servicePrice;
        return $this;
    }

    public function getPriceInvoiced(): ?string
    {
        return $this->priceInvoiced;
    }

    public function setPriceInvoiced(?string $priceInvoiced): static
    {
        $this->priceInvoiced = $priceInvoiced;
        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTimeInterface $startDate): static
    {
        $this->startDate = $startDate;
        return $this;
    }

    public function getFinishDate(): ?\DateTimeInterface
    {
        return $this->finishDate;
    }

    public function setFinishDate(?\DateTimeInterface $finishDate): static
    {
        $this->finishDate = $finishDate;
        return $this;
    }
}
