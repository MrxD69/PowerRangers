<?php

namespace App\Entity;

use App\Repository\ReclamationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReclamationRepository::class)]
class Reclamation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 255)]
    private ?string $etat = null;

    #[ORM\OneToOne(mappedBy: 'reclamation', cascade: ['persist', 'remove'])]
    private ?Reponse $reponse = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le message ne peut pas être vide.')]
    #[Assert\Length(
        min: 10,
        max: 300,
        minMessage: 'Le message doit contenir au moins {{ limit }} caractères.',
        maxMessage: 'Le message ne peut pas dépasser {{ limit }} caractères.'
    )]
    private ?string $message = null;

    #[ORM\ManyToOne(targetEntity: ProjectDb::class, inversedBy: "reclamations")]
    private ?ProjectDb $projectDb = null;

    #[ORM\ManyToOne(targetEntity: CommandeDb::class)]
    private ?CommandeDb $commandeDb = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $complainant = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $againstUser = null;

    // Getters and Setters

    public function getId(): ?int
    {
        return $this->id;
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

    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(string $etat): static
    {
        $this->etat = $etat;
        return $this;
    }

    public function getReponse(): ?Reponse
    {
        return $this->reponse;
    }

    public function setReponse(?Reponse $reponse): static
    {
        if ($reponse && $reponse->getReclamation() !== $this) {
            $reponse->setReclamation($this);
        }

        $this->reponse = $reponse;
        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;
        return $this;
    }

    public function getProjectDb(): ?ProjectDb
    {
        return $this->projectDb;
    }

    public function setProjectDb(?ProjectDb $projectDb): static
    {
        $this->projectDb = $projectDb;
        return $this;
    }

    public function getCommandeDb(): ?CommandeDb
    {
        return $this->commandeDb;
    }

    public function setCommandeDb(?CommandeDb $commandeDb): self
    {
        $this->commandeDb = $commandeDb;
        return $this;
    }

    public function getComplainant(): ?User
    {
        return $this->complainant;
    }

    public function setComplainant(User $complainant): self
    {
        $this->complainant = $complainant;
        return $this;
    }

    public function getAgainstUser(): ?User
    {
        return $this->againstUser;
    }

    public function setAgainstUser(User $againstUser): self
    {
        $this->againstUser = $againstUser;
        return $this;
    }
}
