<?php

namespace App\Entity;

use App\Repository\UserSettingsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserSettingsRepository::class)]
class UserSettings
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: User::class, inversedBy: "settings")]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private User $user;

    #[ORM\Column(type: "integer", options: ["default" => 25])]
    private int $workDuration = 25;

    #[ORM\Column(type: "integer", options: ["default" => 5])]
    private int $shortBreakDuration = 5;

    #[ORM\Column(type: "integer", options: ["default" => 15])]
    private int $longBreakDuration = 15;

    #[ORM\Column(type: "integer", options: ["default" => 4])]
    private int $breakInterval = 4;

    public function getId(): ?int { return $this->id; }
    public function getUser(): User { return $this->user; }
    public function setUser(User $user): self { $this->user = $user; return $this; }
    public function getWorkDuration(): int { return $this->workDuration; }
    public function setWorkDuration(int $workDuration): self { $this->workDuration = $workDuration; return $this; }
    public function getShortBreakDuration(): int { return $this->shortBreakDuration; }
    public function setShortBreakDuration(int $shortBreakDuration): self { $this->shortBreakDuration = $shortBreakDuration; return $this; }
    public function getLongBreakDuration(): int { return $this->longBreakDuration; }
    public function setLongBreakDuration(int $longBreakDuration): self { $this->longBreakDuration = $longBreakDuration; return $this; }
    public function getBreakInterval(): int { return $this->breakInterval; }
    public function setBreakInterval(int $breakInterval): self { $this->breakInterval = $breakInterval; return $this; }
}
