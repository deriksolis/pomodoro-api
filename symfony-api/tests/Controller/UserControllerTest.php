<?php

namespace App\Tests\Controller;

use App\Controller\UserController;
use App\Entity\User;
use App\Entity\UserSettings;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ConstraintViolationList;

class UserControllerTest extends TestCase
{
    private $entityManager;
    private $userRepository;
    private $settingsRepository;
    private $validator;
    private $controller;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);

        $this->userRepository = $this->createMock(ObjectRepository::class);
        $this->settingsRepository = $this->createMock(ObjectRepository::class);

        $this->entityManager
            ->method('getRepository')
            ->willReturnMap([
                [User::class, $this->userRepository],
                [UserSettings::class, $this->settingsRepository],
            ]);

        $this->controller = new UserController($this->entityManager);
    }

    private function setEntityId($entity, int $id): void
    {
        $reflection = new \ReflectionClass($entity);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($entity, $id);
    }

    public function testCreateUserFailsIfUsernameMissing()
    {
        $request = new Request([], [], [], [], [], [], json_encode([]));
        $response = $this->controller->createUser($request);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(json_encode(['message' => 'Username is required']), $response->getContent());
    }

    public function testCreateUserFailsIfUsernameExists()
    {
        $existingUser = new User();
        $this->userRepository->method('findOneBy')->willReturn($existingUser);

        $request = new Request([], [], [], [], [], [], json_encode(['username' => 'existing_user']));
        $response = $this->controller->createUser($request);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(json_encode(['message' => 'Username already exists']), $response->getContent());
    }

    public function testGetUserByIdFailsIfUserNotFound()
    {
        $this->userRepository->method('findOneBy')->willReturn(null);

        $response = $this->controller->getUserById(999);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(json_encode(['message' => 'No user found for id 999']), $response->getContent());
    }

    public function testGetUserByIdReturnsUserData()
    {
        $user = new User();
        $this->setEntityId($user, 1);
        $user->setUsername('test_user');

        $settings = new UserSettings();
        $settings->setUser($user);
        $settings->setWorkDuration(25);
        $settings->setShortBreakDuration(5);
        $settings->setLongBreakDuration(15);
        $settings->setBreakInterval(4);

        $this->userRepository->method('findOneBy')->willReturn($user);
        $this->settingsRepository->method('findOneBy')->willReturn($settings);

        $response = $this->controller->getUserById(1);

        $this->assertEquals(200, $response->getStatusCode());
        $expectedResponse = [
            'username' => 'test_user',
            'settings' => [
                'work_duration' => 25,
                'short_break_duration' => 5,
                'long_break_duration' => 15,
                'break_interval' => 4,
            ]
        ];
        $this->assertJsonStringEqualsJsonString(json_encode($expectedResponse), $response->getContent());
    }

    public function testDeleteUserFailsIfUserNotFound()
    {
        $this->userRepository->method('find')->willReturn(null);

        $response = $this->controller->deleteUser(999);

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(json_encode(['message' => 'User not found']), $response->getContent());
    }

    public function testDeleteUserSucceeds()
    {
        $user = new User();
        $this->setEntityId($user, 1);

        $this->userRepository->method('find')->willReturn($user);

        $response = $this->controller->deleteUser(1);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(json_encode(['message' => 'User deleted successfully']), $response->getContent());
    }

    public function testUpdateUserSettingsFailsIfSettingsNotFound()
    {
        $this->settingsRepository->method('findOneBy')->willReturn(null);

        $request = new Request([], [], [], [], [], [], json_encode(['work_duration' => 30]));
        $response = $this->controller->updateUserSettings($request, 999, $this->validator);

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(json_encode(['message' => 'User Settings not found']), $response->getContent());
    }

    public function testUpdateUserSettingsFailsWithInvalidData()
    {
        $userSettings = new UserSettings();
        $this->setEntityId($userSettings, 1);
        $this->settingsRepository->method('findOneBy')->willReturn($userSettings);
        $this->validator->method('validate')->willReturn(new ConstraintViolationList());

        $request = new Request([], [], [], [], [], [], json_encode(['work_duration' => 'invalid'])); // Invalid data type
        $response = $this->controller->updateUserSettings($request, 1, $this->validator);

        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testUpdateUserSettingsSucceeds()
    {
        $userSettings = new UserSettings();
        $this->setEntityId($userSettings, 1);
        $this->settingsRepository->method('findOneBy')->willReturn($userSettings);
        $this->validator->method('validate')->willReturn(new ConstraintViolationList());

        $request = new Request([], [], [], [], [], [], json_encode(['work_duration' => 30]));
        $response = $this->controller->updateUserSettings($request, 1, $this->validator);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(json_encode(['message' => 'Settings updated succesfully!']), $response->getContent());
    }
}
