<?php

namespace App\Entity;

use App\Repository\EntrepriseWp2Repository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EntrepriseWp2Repository::class)]
class EntrepriseWp2
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'wp2')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Entreprise $entreprise = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $scoreDmao = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $digitalStrategy = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $digitalReadiness = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $humanCentric = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $dataGovernance = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ai = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $green = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $scoreDma1 = null;

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

    public function getScoreDmao(): ?string
    {
        return $this->scoreDmao;
    }

    public function setScoreDmao(?string $scoreDmao): static
    {
        $this->scoreDmao = $scoreDmao;
        return $this;
    }

    public function getDigitalStrategy(): ?string
    {
        return $this->digitalStrategy;
    }

    public function setDigitalStrategy(?string $digitalStrategy): static
    {
        $this->digitalStrategy = $digitalStrategy;
        return $this;
    }

    public function getDigitalReadiness(): ?string
    {
        return $this->digitalReadiness;
    }

    public function setDigitalReadiness(?string $digitalReadiness): static
    {
        $this->digitalReadiness = $digitalReadiness;
        return $this;
    }

    public function getHumanCentric(): ?string
    {
        return $this->humanCentric;
    }

    public function setHumanCentric(?string $humanCentric): static
    {
        $this->humanCentric = $humanCentric;
        return $this;
    }

    public function getDataGovernance(): ?string
    {
        return $this->dataGovernance;
    }

    public function setDataGovernance(?string $dataGovernance): static
    {
        $this->dataGovernance = $dataGovernance;
        return $this;
    }

    public function getAi(): ?string
    {
        return $this->ai;
    }

    public function setAi(?string $ai): static
    {
        $this->ai = $ai;
        return $this;
    }

    public function getGreen(): ?string
    {
        return $this->green;
    }

    public function setGreen(?string $green): static
    {
        $this->green = $green;
        return $this;
    }

    public function getScoreDma1(): ?string
    {
        return $this->scoreDma1;
    }

    public function setScoreDma1(?string $scoreDma1): static
    {
        $this->scoreDma1 = $scoreDma1;
        return $this;
    }
}
