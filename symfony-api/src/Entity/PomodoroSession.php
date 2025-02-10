<?php

namespace App\Entity;

use App\Repository\PomodoroSessionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PomodoroSessionRepository::class)]
class PomodoroSession
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "bigint")]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private User $user;

    #[ORM\Column(type: "datetime")]
    private \DateTime $startedAt;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTime $endedAt = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $taskDescription = null;

    public function getId(): ?int { return $this->id; }

    public function getUser(): User { return $this->user; }
    public function setUser(User $user): self { $this->user = $user; return $this; }

    public function getStartedAt(): \DateTime { return $this->startedAt; }
    public function setStartedAt(\DateTime $startedAt): self { $this->startedAt = $startedAt; return $this; }

    public function getEndedAt(): ?\DateTime { return $this->endedAt; }
    public function setEndedAt(?\DateTime $endedAt): self { $this->endedAt = $endedAt; return $this; }

    public function getTaskDescription(): ?string { return $this->taskDescription; }
    public function setTaskDescription(?string $taskDescription): self { $this->taskDescription = $taskDescription; return $this; }
}