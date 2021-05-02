<?php

namespace App\Entity;

use App\Repository\VilleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=VilleRepository::class)
 */
class Ville
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
     * @ORM\Column(type="string", length=10)
     */
    private $codePostal;

    /**
     * @ORM\OneToMany(targetEntity=Sortie::class, mappedBy="ville")
     */
    private $sorties;

    /**
     * @ORM\OneToMany(targetEntity=Campus::class, mappedBy="ville")
     */
    private $campus;

    // -----------------------------------------------------------------------------------------------------------------
    // -----------------------------------------------------------------------------------------------------------------

    public function __construct()
    {
        $this->sorties = new ArrayCollection();
        $this->campus = new ArrayCollection();
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

    public function getCodePostal(): ?string
    {
        return $this->codePostal;
    }

    public function setCodePostal(string $codePostal): self
    {
        $this->codePostal = $codePostal;

        return $this;
    }

    /**
     * @return Collection|Sortie[]
     */
    public function getSorties(): Collection
    {
        return $this->sorties;
    }

    public function addSortie(Sortie $sorties): self
    {
        if (!$this->sorties->contains($sorties)) {
            $this->sorties[] = $sorties;
            $sorties->setVille($this);
        }

        return $this;
    }

    public function removeSortie(Sortie $sorties): self
    {
        if ($this->sorties->removeElement($sorties)) {
            // set the owning side to null (unless already changed)
            if ($sorties->getVille() === $this) {
                $sorties->setVille(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Campus[]
     */
    public function getCampus(): Collection
    {
        return $this->campus;
    }

    public function addCampus(Campus $campus): self
    {
        if (!$this->campus->contains($campus)) {
            $this->campus[] = $campus;
            $campus->setVille($this);
        }

        return $this;
    }

    public function removeCampus(Campus $campus): self
    {
        if ($this->campus->removeElement($campus)) {
            // set the owning side to null (unless already changed)
            if ($campus->getVille() === $this) {
                $campus->setVille(null);
            }
        }

        return $this;
    }
}
