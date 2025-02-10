<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`users`')]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 255, unique: true)]
    private string $username;

    #[ORM\OneToMany(mappedBy: "user", targetEntity: PomodoroSession::class, cascade: ["remove"])]
    private Collection $sessions;

    #[ORM\OneToOne(mappedBy: "user", targetEntity: UserSettings::class, cascade: ["remove"])]
    private ?UserSettings $settings = null;

    public function __construct()
    {
        $this->sessions = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }
    public function getUsername(): string { return $this->username; }
    public function setUsername(string $username): self { $this->username = $username; return $this; }
    public function getSessions(): Collection { return $this->sessions; }
    public function getSettings(): ?UserSettings { return $this->settings; }
}
