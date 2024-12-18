<?php

namespace App\Entity;

use App\Enum\UserRole;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: "App\Repository\UserRepository")]
#[ORM\Table(name: "user")]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 255, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Email]
    private ?string $email = null;

    #[ORM\Column(type: "string")]
    #[Assert\NotBlank]
    private ?string $password = null;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank]
    private ?string $nom = null;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank]
    private ?string $prenom = null;

    #[ORM\Column(type: "string", enumType: UserRole::class)]
    #[Assert\NotBlank]
    private UserRole $role;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $biography = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $location = null;

    #[ORM\Column(type: "json", nullable: true)]
    private ?array $skills = [];

    #[ORM\Column(type: "json", nullable: true)]
    private ?array $workExperience = [];

    #[ORM\Column(type: "string", length: 15, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $profilePicture = null;

    #[ORM\OneToMany(mappedBy: "client", targetEntity: ProjectDb::class, cascade: ["persist", "remove"])]
    private Collection $projects;

    public function __construct()
    {
        $this->projects = new ArrayCollection();
    }

    // Social Links
    #[ORM\Column(type: "string", length: 255, nullable: true)]
    #[Assert\Url]
    private ?string $twitter = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    #[Assert\Url]
    private ?string $facebook = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    #[Assert\Url]
    private ?string $instagram = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    #[Assert\Url]
    private ?string $linkedin = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    #[Assert\Url]
    private ?string $github = null;

    // Getters and Setters for Social Links
    public function getTwitter(): ?string
    {
        return $this->twitter;
    }

    public function setTwitter(?string $twitter): self
    {
        $this->twitter = $twitter;
        return $this;
    }

    public function getFacebook(): ?string
    {
        return $this->facebook;
    }

    public function setFacebook(?string $facebook): self
    {
        $this->facebook = $facebook;
        return $this;
    }

    public function getInstagram(): ?string
    {
        return $this->instagram;
    }

    public function setInstagram(?string $instagram): self
    {
        $this->instagram = $instagram;
        return $this;
    }

    public function getLinkedin(): ?string
    {
        return $this->linkedin;
    }

    public function setLinkedin(?string $linkedin): self
    {
        $this->linkedin = $linkedin;
        return $this;
    }

    public function getGithub(): ?string
    {
        return $this->github;
    }

    public function setGithub(?string $github): self
    {
        $this->github = $github;
        return $this;
    }

    // General Methods
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
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

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;
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

    public function getBiography(): ?string
    {
        return $this->biography;
    }

    public function setBiography(?string $biography): self
    {
        $this->biography = $biography;
        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): self
    {
        $this->location = $location;
        return $this;
    }

    public function getSkills(): ?array
    {
        return $this->skills;
    }

    public function setSkills(?array $skills): self
    {
        $this->skills = $skills;
        return $this;
    }

    public function getWorkExperience(): ?array
    {
        return $this->workExperience;
    }

    public function setWorkExperience(?array $workExperience): self
    {
        $this->workExperience = $workExperience;
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;
        return $this;
    }

    public function getProfilePicture(): ?string
    {
        return $this->profilePicture;
    }

    public function setProfilePicture(?string $profilePicture): self
    {
        $this->profilePicture = $profilePicture;
        return $this;
    }

    public function getProjects(): Collection
    {
        return $this->projects;
    }

    public function addProject(ProjectDb $project): self
    {
        if (!$this->projects->contains($project)) {
            $this->projects->add($project);
            $project->setClient($this);
        }
        return $this;
    }

    public function removeProject(ProjectDb $project): self
    {
        if ($this->projects->removeElement($project) && $project->getClient() === $this) {
            $project->setClient(null);
        }
        return $this;
    }

    public function getRoles(): array
    {
        return [$this->role->value];
    }

    public function eraseCredentials(): void
    {
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }
}
