<?php

namespace App\Entity;

use App\Repository\DrinkableWaterQualityRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DrinkableWaterQualityRepository::class)]
class DrinkableWaterQuality
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $codeDepartement;

    #[ORM\Column(type: 'string', length: 255)]
    private $nomDepartement;

    #[ORM\Column(type: 'string', length: 255)]
    private $codeCommune;

    #[ORM\Column(type: 'string', length: 255)]
    private $nomCommune;

    #[ORM\Column(type: 'string', length: 255)]
    private $libelleParametre;

    #[ORM\Column(type: 'datetime')]
    private $datePrelevement;

    #[ORM\Column(type: 'float')]
    private $resultatNumerique;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $libelleUnite;

    public function getCodeDepartement(): ?string
    {
        return $this->codeDepartement;
    }

    public function setCodeDepartement(string $codeDepartement): self
    {
        $this->codeDepartement = $codeDepartement;

        return $this;
    }

    public function getNomDepartement(): ?string
    {
        return $this->nomDepartement;
    }

    public function setNomDepartement(string $nomDepartement): self
    {
        $this->nomDepartement = $nomDepartement;

        return $this;
    }

    public function getCodeCommune(): ?string
    {
        return $this->codeCommune;
    }

    public function setCodeCommune(string $codeCommune): self
    {
        $this->codeCommune = $codeCommune;

        return $this;
    }

    public function getNomCommune(): ?string
    {
        return $this->nomCommune;
    }

    public function setNomCommune(string $nomCommune): self
    {
        $this->nomCommune = $nomCommune;

        return $this;
    }

    public function getLibelleParametre(): ?string
    {
        return $this->libelleParametre;
    }

    public function setLibelleParametre(string $libelleParametre): self
    {
        $this->libelleParametre = $libelleParametre;

        return $this;
    }

    public function getDatePrelevement(): ?\DateTimeInterface
    {
        return $this->datePrelevement;
    }

    public function setDatePrelevement(\DateTimeInterface $datePrelevement): self
    {
        $this->datePrelevement = $datePrelevement;

        return $this;
    }

    public function getResultatNumerique(): ?float
    {
        return $this->resultatNumerique;
    }

    public function setResultatNumerique(float $resultatNumerique): self
    {
        $this->resultatNumerique = $resultatNumerique;

        return $this;
    }

    public function getLibelleUnite(): ?string
    {
        return $this->libelleUnite;
    }

    public function setLibelleUnite(?string $libelleUnite): self
    {
        $this->libelleUnite = $libelleUnite;

        return $this;
    }


}
