<?php

namespace App\Controller;

use App\Entity\PomodoroSession;
use App\Entity\User;
use App\Entity\UserSettings;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Util\Json;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/timer')]
final class TimerController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/start', methods: ['POST'])]
    public function startTimer(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $userID = $data['user_id'] ?? null;

        if (!isset($userID)) {
            return new JsonResponse(['message' => 'User ID is required!'], 400);
        }

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['id' => $userID]);
        $userSettings = $this->entityManager->getRepository(UserSettings::class)->findOneBy(['user' => $user]);

        if (!isset($user) || !isset($userSettings)) {
            return new JsonResponse(['message' => 'No user settings found for given ID'], 400);
        }

        $activeSession = $this->entityManager->getRepository(PomodoroSession::class)->findOneBy([
            'user' => $userID,
            'endedAt' => null
        ]);
    
        if ($activeSession) {
            return new JsonResponse([
                'message' => 'An active session already exists. Stop the current session before starting a new one.',
                'session' => [
                    'id' => $activeSession->getId(),
                    'started_at' => $activeSession->getStartedAt()->format('Y-m-d H:i:s'),
                    'task_description' => $activeSession->getTaskDescription(),
                ]
            ], 400);
        }

        $task_description = $data['task_description'] ?? null;

        $session = new PomodoroSession();

        $session->setTaskDescription($task_description);
        $session->setStartedAt(new \DateTime());
        $session->setUser($user);

        $this->entityManager->persist($session);
        $this->entityManager->flush();

        return new JsonResponse([
            'message' => 'Timer started successfully',
            'user' => [
                'id' => $userID,
                'username' => $user->getUsername(),
            ],
            'session' => [
                'session' => $session->getId(),
                'startedAt' => $session->getStartedAt()->format('Y-m-d H:i:s'),
                'workDuration' => $userSettings->getWorkDuration(),
                'shortBreakDuration' => $userSettings->getShortBreakDuration(),
                'longBreakDuration' => $userSettings->getLongBreakDuration(),
                'breakInterval' => $userSettings->getBreakInterval(),
            ]
        ]);
    }

    #[Route('/stop/{userId}/{sessionId}', methods: ['PUT'])]
    public function stopTimer(int $userId, int $sessionId): JsonResponse
    {
        $session = $this->entityManager->getRepository(PomodoroSession::class)->find($sessionId);

        if (!$session) {
            return new JsonResponse(['message' => 'Session not found'], 404);
        }

        if ($session->getUser()->getId() !== $userId) {
            return new JsonResponse(['message' => 'User who created the session must be the one who stops it'], 400);
        }

        if ($session->getEndedAt()) {
            return new JsonResponse(['message' => 'Session already stopped'], 400);
        }

        $session->setEndedAt(new \DateTime());
        $this->entityManager->flush();

        return new JsonResponse([
            'message' => 'Timer stopped successfully',
            'session' => [
                'id' => $session->getId(),
                'user_id' => $session->getUser()->getId(),
                'started_at' => $session->getStartedAt()->format('Y-m-d H:i:s'),
                'ended_at' => $session->getEndedAt()->format('Y-m-d H:i:s')
            ]
        ]);
    }

    #[Route('/sessions/{userId}', methods: ['GET'])]
    public function getUserSessions(int $userId): JsonResponse
    {
        $user = $this->entityManager->getRepository(User::class)->find($userId);

        if (!$user) {
            return new JsonResponse(['message' => 'User not found'], 404);
        }

        $sessions = $this->entityManager->getRepository(PomodoroSession::class)->findBy(
            ['user' => $userId],
            ['startedAt' => 'DESC']
        );

        $sessionData = array_map(fn($session) => [
            'id' => $session->getId(),
            'started_at' => $session->getStartedAt()->format('Y-m-d H:i:s'),
            'ended_at' => $session->getEndedAt()?->format('Y-m-d H:i:s'),
            'task_description' => $session->getTaskDescription()
        ], $sessions);

        return new JsonResponse([
            'user' => [
                'id' => $user->getId(),
                'username' => $user->getUsername()
            ],
            'sessions' => $sessionData
        ]);
    }
}
