<?php

namespace App\Entity;

use App\Repository\EvenementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EvenementRepository::class)]
class Evenement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom de l'événement est obligatoire.")]
    #[Assert\Length(min: 3, minMessage: "Le nom doit contenir au moins 3 caractères.")]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le type de l'événement est obligatoire.")]
    private ?string $type = null;

 #[ORM\Column(type: Types::DATE_MUTABLE)]
 private ?\DateTimeInterface $date = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "Le nombre de places est obligatoire.")]
    #[Assert\GreaterThanOrEqual(1, message: "Le nombre de places doit être au moins 1.")]
    private ?int $nbre_dispo = null;

    #[ORM\Column]
    private ?int $id_user = null;

    public function getIdUser(): ?int
    {
        return $this->id_user;
    }

    public function setIdUser(int $idUser): static
    {
        $this->id_user = $idUser;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getNbreDispo(): ?int
    {
        return $this->nbre_dispo;
    }

    public function setNbreDispo(int $nbre_dispo): static
    {
        $this->nbre_dispo = $nbre_dispo;

        return $this;
    }
}
