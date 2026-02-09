<?php

namespace App\Entity;

use App\Repository\EntrepriseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EntrepriseRepository::class)]
class Entreprise
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $anneeEdih = null;

    #[ORM\Column(length: 100)]
    private ?string $typeStructure = null;

    #[ORM\Column]
    private ?int $anneeCreation = null;

    #[ORM\Column(length: 255)]
    private ?string $secteur = null;

    #[ORM\Column(length: 14)]
    private ?string $siret = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $taille = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $chiffreAffaires = null;

    #[ORM\Column(length: 10)]
    private ?string $codePostal = null;

    #[ORM\Column(length: 100)]
    private ?string $ville = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $region = null;

    #[ORM\Column(length: 100)]
    private ?string $pays = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $adresse = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\OneToOne(mappedBy: 'entreprise', cascade: ['persist', 'remove'])]
    private ?EntrepriseWp2 $wp2 = null;

    #[ORM\OneToMany(targetEntity: EntrepriseWp5Event::class, mappedBy: 'entreprise', cascade: ['persist', 'remove'])]
    private Collection $wp5Events;

    #[ORM\OneToMany(targetEntity: EntrepriseWp5Formation::class, mappedBy: 'entreprise', cascade: ['persist', 'remove'])]
    private Collection $wp5Formations;

    #[ORM\OneToMany(targetEntity: EntrepriseWp6::class, mappedBy: 'entreprise', cascade: ['persist', 'remove'])]
    private Collection $wp6;

    #[ORM\OneToMany(targetEntity: EntrepriseWp7::class, mappedBy: 'entreprise', cascade: ['persist', 'remove'])]
    private Collection $wp7;

    #[ORM\OneToMany(targetEntity: EntrepriseMiseEnRelation::class, mappedBy: 'entreprise', cascade: ['persist', 'remove'])]
    private Collection $miseEnRelations;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $proprietaire = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $statut = null;

    public function __construct()
    {
        $this->wp5Events = new ArrayCollection();
        $this->wp5Formations = new ArrayCollection();
        $this->wp6 = new ArrayCollection();
        $this->wp7 = new ArrayCollection();
        $this->miseEnRelations = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->statut = 'vert'; // Statut par défaut
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function getAnneeEdih(): ?string
    {
        return $this->anneeEdih;
    }

    public function setAnneeEdih(?string $anneeEdih): static
    {
        $this->anneeEdih = $anneeEdih;
        return $this;
    }

    public function getTypeStructure(): ?string
    {
        return $this->typeStructure;
    }

    public function setTypeStructure(string $typeStructure): static
    {
        $this->typeStructure = $typeStructure;
        return $this;
    }

    public function getAnneeCreation(): ?int
    {
        return $this->anneeCreation;
    }

    public function setAnneeCreation(int $anneeCreation): static
    {
        $this->anneeCreation = $anneeCreation;
        return $this;
    }

    public function getSecteur(): ?string
    {
        return $this->secteur;
    }

    public function setSecteur(string $secteur): static
    {
        $this->secteur = $secteur;
        return $this;
    }

    public function getSiret(): ?string
    {
        return $this->siret;
    }

    public function setSiret(string $siret): static
    {
        $this->siret = $siret;
        return $this;
    }

    public function getTaille(): ?string
    {
        return $this->taille;
    }

    public function setTaille(?string $taille): static
    {
        $this->taille = $taille;
        return $this;
    }

    public function getChiffreAffaires(): ?string
    {
        return $this->chiffreAffaires;
    }

    public function setChiffreAffaires(?string $chiffreAffaires): static
    {
        $this->chiffreAffaires = $chiffreAffaires;
        return $this;
    }

    public function getCodePostal(): ?string
    {
        return $this->codePostal;
    }

    public function setCodePostal(string $codePostal): static
    {
        $this->codePostal = $codePostal;
        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(string $ville): static
    {
        $this->ville = $ville;
        return $this;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(?string $region): static
    {
        $this->region = $region;
        return $this;
    }

    public function getPays(): ?string
    {
        return $this->pays;
    }

    public function setPays(string $pays): static
    {
        $this->pays = $pays;
        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): static
    {
        $this->adresse = $adresse;
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

    public function getWp2(): ?EntrepriseWp2
    {
        return $this->wp2;
    }

    public function setWp2(?EntrepriseWp2 $wp2): static
    {
        if ($wp2 === null && $this->wp2 !== null) {
            $this->wp2->setEntreprise(null);
        }

        if ($wp2 !== null && $wp2->getEntreprise() !== $this) {
            $wp2->setEntreprise($this);
        }

        $this->wp2 = $wp2;
        return $this;
    }

    public function getWp5Events(): Collection
    {
        return $this->wp5Events;
    }

    public function addWp5Event(EntrepriseWp5Event $wp5Event): static
    {
        if (!$this->wp5Events->contains($wp5Event)) {
            $this->wp5Events->add($wp5Event);
            $wp5Event->setEntreprise($this);
        }
        return $this;
    }

    public function removeWp5Event(EntrepriseWp5Event $wp5Event): static
    {
        if ($this->wp5Events->removeElement($wp5Event)) {
            if ($wp5Event->getEntreprise() === $this) {
                $wp5Event->setEntreprise(null);
            }
        }
        return $this;
    }

    public function getWp5Formations(): Collection
    {
        return $this->wp5Formations;
    }

    public function addWp5Formation(EntrepriseWp5Formation $wp5Formation): static
    {
        if (!$this->wp5Formations->contains($wp5Formation)) {
            $this->wp5Formations->add($wp5Formation);
            $wp5Formation->setEntreprise($this);
        }
        return $this;
    }

    public function removeWp5Formation(EntrepriseWp5Formation $wp5Formation): static
    {
        if ($this->wp5Formations->removeElement($wp5Formation)) {
            if ($wp5Formation->getEntreprise() === $this) {
                $wp5Formation->setEntreprise(null);
            }
        }
        return $this;
    }

    public function getWp6(): Collection
    {
        return $this->wp6;
    }

    public function addWp6(EntrepriseWp6 $wp6): static
    {
        if (!$this->wp6->contains($wp6)) {
            $this->wp6->add($wp6);
            $wp6->setEntreprise($this);
        }
        return $this;
    }

    public function removeWp6(EntrepriseWp6 $wp6): static
    {
        if ($this->wp6->removeElement($wp6)) {
            if ($wp6->getEntreprise() === $this) {
                $wp6->setEntreprise(null);
            }
        }
        return $this;
    }

    public function getWp7(): Collection
    {
        return $this->wp7;
    }

    public function addWp7(EntrepriseWp7 $wp7): static
    {
        if (!$this->wp7->contains($wp7)) {
            $this->wp7->add($wp7);
            $wp7->setEntreprise($this);
        }
        return $this;
    }

    public function removeWp7(EntrepriseWp7 $wp7): static
    {
        if ($this->wp7->removeElement($wp7)) {
            if ($wp7->getEntreprise() === $this) {
                $wp7->setEntreprise(null);
            }
        }
        return $this;
    }

    public function getMiseEnRelations(): Collection
    {
        return $this->miseEnRelations;
    }

    public function addMiseEnRelation(EntrepriseMiseEnRelation $miseEnRelation): static
    {
        if (!$this->miseEnRelations->contains($miseEnRelation)) {
            $this->miseEnRelations->add($miseEnRelation);
            $miseEnRelation->setEntreprise($this);
        }
        return $this;
    }

    public function removeMiseEnRelation(EntrepriseMiseEnRelation $miseEnRelation): static
    {
        if ($this->miseEnRelations->removeElement($miseEnRelation)) {
            if ($miseEnRelation->getEntreprise() === $this) {
                $miseEnRelation->setEntreprise(null);
            }
        }
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getProprietaire(): ?User
    {
        return $this->proprietaire;
    }

    public function setProprietaire(?User $proprietaire): static
    {
        $this->proprietaire = $proprietaire;
        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(?string $statut): static
    {
        $this->statut = $statut;
        return $this;
    }
}
