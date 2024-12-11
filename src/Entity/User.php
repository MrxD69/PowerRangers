<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use App\Enum\UserRole;

#[ORM\Entity(repositoryClass: "App\Repository\UserRepository")]
#[ORM\Table(name: "user")]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "string", length: 255, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Email]
    private string $email;

    #[ORM\Column(type: "string")]
    #[Assert\NotBlank]
    private string $password;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank]
    private string $nom;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank]
    private string $prenom;

    #[ORM\Column(type: "string", length: 15, nullable: true)]
    private ?string $tel;

    #[ORM\Column(type: "string", enumType: UserRole::class)]
    #[Assert\NotBlank]
    private UserRole $role;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $freelancerBio = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $clientCompany = null;

    #[ORM\Column(type: "boolean", nullable: true)]
    private ?bool $adminPrivileges = null;

    #[ORM\Column(type: "string", nullable: true)]
    private ?string $profilePicture = null;

    // Getters and setters
    public function getId(): int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getPrenom(): string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;
        return $this;
    }

    public function getTel(): ?string
    {
        return $this->tel;
    }

    public function setTel(?string $tel): self
    {
        $this->tel = $tel;
        return $this;
    }

    public function getRole(): UserRole
    {
        return $this->role;
    }

    public function setRole(UserRole $role): self
    {
        $this->role = $role;
        return $this;
    }

    public function getFreelancerBio(): ?string
    {
        return $this->freelancerBio;
    }

    public function setFreelancerBio(?string $freelancerBio): self
    {
        $this->freelancerBio = $freelancerBio;
        return $this;
    }

    public function getClientCompany(): ?string
    {
        return $this->clientCompany;
    }

    public function setClientCompany(?string $clientCompany): self
    {
        $this->clientCompany = $clientCompany;
        return $this;
    }

    public function hasAdminPrivileges(): ?bool
    {
        return $this->adminPrivileges;
    }

    public function setAdminPrivileges(?bool $adminPrivileges): self
    {
        $this->adminPrivileges = $adminPrivileges;
        return $this;
    }

    public function getRoles(): array
    {
        // Symfony expects an array of roles
        return [$this->role->value];
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function setProfilePicture(?string $profilePicture): self
    {
        $this->profilePicture = $profilePicture;
        return $this;
    }

    public function eraseCredentials(): void
    {
        // If there are sensitive data fields, clear them here
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }
}