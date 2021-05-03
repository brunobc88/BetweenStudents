<?php

namespace App\Entity;

use App\Repository\SortieRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SortieRepository::class)
 */
class Sortie
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nom;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="integer")
     */
    private $nbreInscriptionMax;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateDebut;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateClotureInscription;

    /**
     * @ORM\Column(type="integer")
     */
    private $duree;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $rue;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateAnnulation;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $raisonAnnulation;

    /**
     * @ORM\ManyToOne(targetEntity=Ville::class, inversedBy="sorties")
     * @ORM\JoinColumn(nullable=false)
     */
    private $ville;

    /**
     * @ORM\OneToMany(targetEntity=SortieImage::class, mappedBy="sortie", orphanRemoval=true)
     */
    private $images;

    /**
     * @ORM\OneToMany(targetEntity=SortieCommentaire::class, mappedBy="sortie", orphanRemoval=true)
     */
    private $commentaires;

    /**
     * @ORM\ManyToOne(targetEntity=Etat::class, inversedBy="sorties")
     * @ORM\JoinColumn(nullable=false)
     */
    private $etat;

    /**
     * @ORM\ManyToOne(targetEntity=Campus::class, inversedBy="sorties")
     * @ORM\JoinColumn(nullable=false)
     */
    private $campus;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, inversedBy="sortiesAsParticipant")
     */
    private $participants;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="sortiesAsOrganisateur")
     * @ORM\JoinColumn(nullable=false)
     */
    private $organisateur;

    // -----------------------------------------------------------------------------------------------------------------
    // -----------------------------------------------------------------------------------------------------------------

    public function __construct()
    {
        $this->images = new ArrayCollection();
        $this->commentaires = new ArrayCollection();
        $this->participants = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getNbreInscriptionMax(): ?int
    {
        return $this->nbreInscriptionMax;
    }

    public function setNbreInscriptionMax(int $nbreInscriptionMax): self
    {
        $this->nbreInscriptionMax = $nbreInscriptionMax;

        return $this;
    }

    public function getDateDebut(): ?DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(DateTimeInterface $dateDebut): self
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    public function getDateClotureInscription(): ?DateTimeInterface
    {
        return $this->dateClotureInscription;
    }

    public function setDateClotureInscription(DateTimeInterface $dateClotureInscription): self
    {
        $this->dateClotureInscription = $dateClotureInscription;

        return $this;
    }

    public function getDuree(): ?int
    {
        return $this->duree;
    }

    public function setDuree(int $duree): self
    {
        $this->duree = $duree;

        return $this;
    }

    public function getRue(): ?string
    {
        return $this->rue;
    }

    public function setRue(string $rue): self
    {
        $this->rue = $rue;

        return $this;
    }

    public function getDateAnnulation(): ?DateTimeInterface
    {
        return $this->dateAnnulation;
    }

    public function setDateAnnulation(?DateTimeInterface $dateAnnulation): self
    {
        $this->dateAnnulation = $dateAnnulation;

        return $this;
    }

    public function getRaisonAnnulation(): ?string
    {
        return $this->raisonAnnulation;
    }

    public function setRaisonAnnulation(?string $raisonAnnulation): self
    {
        $this->raisonAnnulation = $raisonAnnulation;

        return $this;
    }

    public function getVille(): ?Ville
    {
        return $this->ville;
    }

    public function setVille(?Ville $ville): self
    {
        $this->ville = $ville;

        return $this;
    }

    /**
     * @return Collection|SortieImage[]
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(SortieImage $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images[] = $image;
            $image->setSortie($this);
        }

        return $this;
    }

    public function removeImage(SortieImage $image): self
    {
        if ($this->images->removeElement($image)) {
            // set the owning side to null (unless already changed)
            if ($image->getSortie() === $this) {
                $image->setSortie(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|SortieCommentaire[]
     */
    public function getCommentaires(): Collection
    {
        return $this->commentaires;
    }

    public function addCommentaire(SortieCommentaire $commentaire): self
    {
        if (!$this->commentaires->contains($commentaire)) {
            $this->commentaires[] = $commentaire;
            $commentaire->setSortie($this);
        }

        return $this;
    }

    public function removeCommentaire(SortieCommentaire $commentaire): self
    {
        if ($this->commentaires->removeElement($commentaire)) {
            // set the owning side to null (unless already changed)
            if ($commentaire->getSortie() === $this) {
                $commentaire->setSortie(null);
            }
        }

        return $this;
    }

    public function getEtat(): ?Etat
    {
        return $this->etat;
    }

    public function setEtat(?Etat $etat): self
    {
        $this->etat = $etat;

        return $this;
    }

    public function getCampus(): ?Campus
    {
        return $this->campus;
    }

    public function setCampus(?Campus $campus): self
    {
        $this->campus = $campus;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(User $participant): self
    {
        if (!$this->participants->contains($participant)) {
            $this->participants[] = $participant;
        }

        return $this;
    }

    public function removeParticipant(User $participant): self
    {
        $this->participants->removeElement($participant);

        return $this;
    }

    public function getOrganisateur(): ?User
    {
        return $this->organisateur;
    }

    public function setOrganisateur(?User $organisateur): self
    {
        $this->organisateur = $organisateur;

        return $this;
    }
}
