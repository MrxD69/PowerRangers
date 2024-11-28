<?php

namespace App\Entity;

use App\Repository\AdminRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AdminRepository::class)]
class Admin extends User
{
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $privileges = [];

    public function __construct()
    {
        parent::__construct();
        $this->setRole('admin');
    }

    public function getPrivileges(): ?array
    {
        return $this->privileges;
    }

    public function setPrivileges(?array $privileges): self
    {
        $this->privileges = $privileges;
        return $this;
    }
}