<?php

namespace App\Entity;

use App\Repository\FreelancerRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FreelancerRepository::class)]
class Freelancer extends User
{
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $portfolioUrl = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $rating = null;

    public function __construct()
    {
        parent::__construct();
        $this->setRole('freelancer');
    }

    public function getPortfolioUrl(): ?string
    {
        return $this->portfolioUrl;
    }

    public function setPortfolioUrl(?string $portfolioUrl): self
    {
        $this->portfolioUrl = $portfolioUrl;
        return $this;
    }

    public function getRating(): ?float
    {
        return $this->rating;
    }

    public function setRating(?float $rating): self
    {
        $this->rating = $rating;
        return $this;
    }
}