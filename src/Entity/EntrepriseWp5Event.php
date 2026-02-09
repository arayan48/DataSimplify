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

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $eventNameEnglish = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $eventNameOriginal = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $edihCoOrganiser = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $coOrganiser = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\Column(nullable: true)]
    private ?int $attendeesNumber = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $deliveryMode = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $websiteUrl = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $mainTechnologies = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $serviceCategory = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $mainSectors = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $eventDescription = null;

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

    public function getEventNameEnglish(): ?string
    {
        return $this->eventNameEnglish;
    }

    public function setEventNameEnglish(?string $eventNameEnglish): static
    {
        $this->eventNameEnglish = $eventNameEnglish;
        return $this;
    }

    public function getEventNameOriginal(): ?string
    {
        return $this->eventNameOriginal;
    }

    public function setEventNameOriginal(?string $eventNameOriginal): static
    {
        $this->eventNameOriginal = $eventNameOriginal;
        return $this;
    }

    public function getEdihCoOrganiser(): ?string
    {
        return $this->edihCoOrganiser;
    }

    public function setEdihCoOrganiser(?string $edihCoOrganiser): static
    {
        $this->edihCoOrganiser = $edihCoOrganiser;
        return $this;
    }

    public function getCoOrganiser(): ?string
    {
        return $this->coOrganiser;
    }

    public function setCoOrganiser(?string $coOrganiser): static
    {
        $this->coOrganiser = $coOrganiser;
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

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): static
    {
        $this->endDate = $endDate;
        return $this;
    }

    public function getAttendeesNumber(): ?int
    {
        return $this->attendeesNumber;
    }

    public function setAttendeesNumber(?int $attendeesNumber): static
    {
        $this->attendeesNumber = $attendeesNumber;
        return $this;
    }

    public function getDeliveryMode(): ?string
    {
        return $this->deliveryMode;
    }

    public function setDeliveryMode(?string $deliveryMode): static
    {
        $this->deliveryMode = $deliveryMode;
        return $this;
    }

    public function getWebsiteUrl(): ?string
    {
        return $this->websiteUrl;
    }

    public function setWebsiteUrl(?string $websiteUrl): static
    {
        $this->websiteUrl = $websiteUrl;
        return $this;
    }

    public function getMainTechnologies(): ?string
    {
        return $this->mainTechnologies;
    }

    public function setMainTechnologies(?string $mainTechnologies): static
    {
        $this->mainTechnologies = $mainTechnologies;
        return $this;
    }

    public function getServiceCategory(): ?string
    {
        return $this->serviceCategory;
    }

    public function setServiceCategory(?string $serviceCategory): static
    {
        $this->serviceCategory = $serviceCategory;
        return $this;
    }

    public function getMainSectors(): ?string
    {
        return $this->mainSectors;
    }

    public function setMainSectors(?string $mainSectors): static
    {
        $this->mainSectors = $mainSectors;
        return $this;
    }

    public function getEventDescription(): ?string
    {
        return $this->eventDescription;
    }

    public function setEventDescription(?string $eventDescription): static
    {
        $this->eventDescription = $eventDescription;
        return $this;
    }
}
