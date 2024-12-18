<?php

namespace App\Entity;

use App\Repository\ProjectDbRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProjectDbRepository::class)]
class ProjectDb
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le domaine ne peut pas être vide.")]
    #[Assert\Length(
        max: 30,
        maxMessage: "Le domaine ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $domaine = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(
        min: 10,
        max: 300,
        minMessage: "La description doit comporter au moins {{ limit }} caractères.",
        maxMessage: "La description ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $description = null;

    #[ORM\ManyToOne(targetEntity: "App\Entity\User", inversedBy: "projects")]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $client = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDomaine(): ?string
    {
        return $this->domaine;
    }

    public function setDomaine(string $domaine): self
    {
        $this->domaine = $domaine;
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

    public function getClient(): ?User
    {
        return $this->client;
    }

    public function setClient(?User $client): self
    {
        $this->client = $client;
        return $this;
    }
}
