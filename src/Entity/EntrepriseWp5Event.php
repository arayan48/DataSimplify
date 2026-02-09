<?php

namespace App\Entity;

use App\Repository\EntrepriseWp5EventRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EntrepriseWp5EventRepository::class)]
class EntrepriseWp5Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'wp5Events')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Entreprise $entreprise = null;

    #[ORM\Column(nullable: true)]
    private ?int $year = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $passage = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $responsableWp5 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $responsableWp4 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $needWp5 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $actionWp5 = null;

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

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(?int $year): static
    {
        $this->year = $year;
        return $this;
    }

    public function getPassage(): ?string
    {
        return $this->passage;
    }

    public function setPassage(?string $passage): static
    {
        $this->passage = $passage;
        return $this;
    }

    public function getResponsableWp5(): ?string
    {
        return $this->responsableWp5;
    }

    public function setResponsableWp5(?string $responsableWp5): static
    {
        $this->responsableWp5 = $responsableWp5;
        return $this;
    }

    public function getResponsableWp4(): ?string
    {
        return $this->responsableWp4;
    }

    public function setResponsableWp4(?string $responsableWp4): static
    {
        $this->responsableWp4 = $responsableWp4;
        return $this;
    }

    public function getNeedWp5(): ?string
    {
        return $this->needWp5;
    }

    public function setNeedWp5(?string $needWp5): static
    {
        $this->needWp5 = $needWp5;
        return $this;
    }

    public function getActionWp5(): ?string
    {
        return $this->actionWp5;
    }

    public function setActionWp5(?string $actionWp5): static
    {
        $this->actionWp5 = $actionWp5;
        return $this;
    }
}
