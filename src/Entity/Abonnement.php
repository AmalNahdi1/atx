<?php

namespace App\Entity;

use App\Repository\AbonnementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AbonnementRepository::class)]
class Abonnement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $typeAb = null;

    #[ORM\Column]
    private ?float $prixAb = null;

    #[ORM\Column(length: 255)]
    private ?string $modePaiementAb = null;

    #[ORM\OneToMany(mappedBy: 'id_abonnement', targetEntity: Contrat::class)]
    private Collection $contrats;

    public function __construct()
    {
        $this->contrats = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTypeAb(): ?string
    {
        return $this->typeAb;
    }

    public function setTypeAb(string $typeAb): self
    {
        $this->typeAb = $typeAb;

        return $this;
    }

    public function getPrixAb(): ?float
    {
        return $this->prixAb;
    }

    public function setPrixAb(float $prixAb): self
    {
        $this->prixAb = $prixAb;

        return $this;
    }

    public function getModePaiementAb(): ?string
    {
        return $this->modePaiementAb;
    }

    public function setModePaiementAb(string $modePaiementAb): self
    {
        $this->modePaiementAb = $modePaiementAb;

        return $this;
    }

    /**
     * @return Collection<int, Contrat>
     */
    public function getContrats(): Collection
    {
        return $this->contrats;
    }

    public function addContrat(Contrat $contrat): self
    {
        if (!$this->contrats->contains($contrat)) {
            $this->contrats->add($contrat);
            $contrat->setIdAbonnement($this);
        }

        return $this;
    }

    public function removeContrat(Contrat $contrat): self
    {
        if ($this->contrats->removeElement($contrat)) {
            // set the owning side to null (unless already changed)
            if ($contrat->getIdAbonnement() === $this) {
                $contrat->setIdAbonnement(null);
            }
        }

        return $this;
    }
}
