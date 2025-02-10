<?php

namespace App\Tests\Controller;

use App\Controller\TimerController;
use App\Entity\PomodoroSession;
use App\Entity\User;
use App\Entity\UserSettings;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ObjectRepository;

class TimerControllerTest extends TestCase
{
    private $entityManager;
    private $userRepository;
    private $sessionRepository;
    private $settingsRepository;
    private $controller;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->userRepository = $this->createMock(ObjectRepository::class);
        $this->sessionRepository = $this->createMock(ObjectRepository::class);
        $this->settingsRepository = $this->createMock(ObjectRepository::class);

        $this->entityManager
            ->method('getRepository')
            ->willReturnMap([
                [User::class, $this->userRepository],
                [PomodoroSession::class, $this->sessionRepository],
                [UserSettings::class, $this->settingsRepository],
            ]);

        $this->controller = new TimerController($this->entityManager);
    }

    private function setEntityId($entity, int $id): void
    {
        $reflection = new \ReflectionClass($entity);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($entity, $id);
    }

    public function testStartTimerFailsWithoutUserId()
    {
        $request = new Request([], [], [], [], [], [], json_encode([]));
        $response = $this->controller->startTimer($request);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(json_encode(['message' => 'User ID is required!']), $response->getContent());
    }

    public function testStartTimerFailsIfUserNotFound()
    {
        $this->userRepository->method('findOneBy')->willReturn(null);

        $request = new Request([], [], [], [], [], [], json_encode(['user_id' => 999]));
        $response = $this->controller->startTimer($request);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(json_encode(['message' => 'No user settings found for given ID']), $response->getContent());
    }

    public function testStartTimerFailsIfActiveSessionExists()
    {
        $user = new User();
        $this->setEntityId($user, 1);

        $session = new PomodoroSession();
        $session->setUser($user);
        $session->setStartedAt(new \DateTime());

        $this->userRepository->method('findOneBy')->willReturn($user);
        $this->settingsRepository->method('findOneBy')->willReturn(new UserSettings());
        $this->sessionRepository->method('findOneBy')->willReturn($session);

        $request = new Request([], [], [], [], [], [], json_encode(['user_id' => 1]));
        $response = $this->controller->startTimer($request);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertStringContainsString('An active session already exists', $response->getContent());
    }

    public function testStopTimerFailsIfSessionNotFound()
    {
        $this->sessionRepository->method('find')->willReturn(null);

        $response = $this->controller->stopTimer(1, 999);

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(json_encode(['message' => 'Session not found']), $response->getContent());
    }

    public function testStopTimerFailsIfUserMismatch()
    {
        $user1 = new User();
        $this->setEntityId($user1, 1);
        
        $user2 = new User();
        $this->setEntityId($user2, 2);

        $session = new PomodoroSession();
        $session->setUser($user1);

        $this->sessionRepository->method('find')->willReturn($session);

        $response = $this->controller->stopTimer(2, 1);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(json_encode(['message' => 'User who created the session must be the one who stops it']), $response->getContent());
    }

    public function testGetUserSessionsFailsIfUserNotFound()
    {
        $this->userRepository->method('find')->willReturn(null);

        $response = $this->controller->getUserSessions(999);

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(json_encode(['message' => 'User not found']), $response->getContent());
    }

    public function testGetUserSessionsReturnsSessions()
    {
        $user = new User();
        $this->setEntityId($user, 1);
        $user->setUsername('john_doe');

        $session1 = new PomodoroSession();
        $this->setEntityId($session1, 100);
        $session1->setUser($user);
        $session1->setStartedAt(new \DateTime('2025-02-07 14:00:00'));

        $session2 = new PomodoroSession();
        $this->setEntityId($session2, 101);
        $session2->setUser($user);
        $session2->setStartedAt(new \DateTime('2025-02-07 15:00:00'));
        $session2->setEndedAt(new \DateTime('2025-02-07 15:25:00'));

        $this->userRepository->method('find')->willReturn($user);
        $this->sessionRepository->method('findBy')->willReturn([$session1, $session2]);

        $response = $this->controller->getUserSessions(1);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertEquals(2, count($data['sessions']));
        $this->assertEquals(100, $data['sessions'][0]['id']);
        $this->assertEquals(101, $data['sessions'][1]['id']);
    }
}
