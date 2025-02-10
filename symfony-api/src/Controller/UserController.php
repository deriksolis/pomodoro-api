<?php

namespace App\Controller;

use Doctrine\ORM\EntityManager;
use App\Entity\User;
use App\Entity\UserSettings;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Util\Json;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/user')]
final class UserController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/create', methods: ['POST'])]
    public function createUser(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $username = $data['username'] ?? null;

        if (!isset($username)) {
            return new JsonResponse(['message' => 'Username is required'], 400);
        }

        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $username]);
        if ($existingUser) {
            return new JsonResponse(['message' => 'Username already exists'], 400);
        }

        $user = new User();
        $user->setUsername($username);

        $userSettings = new UserSettings();
        $userSettings->setUser($user);

        $this->entityManager->persist($user);
        $this->entityManager->persist($userSettings);
        $this->entityManager->flush();

        return new JsonResponse([
            'message' => 'User created succesfully!',
            'user' => [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
            ],
            'settings' => [
                'work_duration' => $userSettings->getWorkDuration(),
                'short_break_duration' => $userSettings->getShortBreakDuration(),
                'long_break_duration' => $userSettings->getLongBreakDuration(),
                'break_interval' => $userSettings->getBreakInterval(),
            ]
        ], 201);
    }

    #[Route('/{id}', methods: 'GET')]
    public function getUserById(Int $id): JsonResponse
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['id' => $id]);

        if (!isset($user)) {
            return new JsonResponse(['message' => 'No user found for id ' . $id]);
        }

        return new JsonResponse([
            'username' => $user->getUsername()
        ], 200);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function deleteUser(int $id): JsonResponse
    {
        $user = $this->entityManager->getRepository(User::class)->find($id);
        if (!$user) {
            return new JsonResponse(['message' => 'User not found'], 404);
        }

        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'User deleted successfully'], 204);
    }

    #[Route('/update-settings/{id}', methods: ['PUT'])]
    public function updateUserSettings(Request $request, int $id, ValidatorInterface $validator): JsonResponse
    {
        $userSettings = $this->entityManager->getRepository(UserSettings::class)->findOneBy(['user' => $id]);
        if (!isset($userSettings)) {
            return new JsonResponse(['message' => 'User Settings not found'], 404);
        }

        $data = json_decode($request->getContent(), true);
        if (!isset($data)) {
            return new JsonResponse(['message' => 'Missing or invalid request data']);
        }

        foreach ($data as $field => $value) {
            $setterMethod = 'set' . ucfirst($field);

            if (method_exists($userSettings, $setterMethod)) {
                $userSettings->$setterMethod($value);
            }
        }

        $errors = $validator->validate($userSettings);
        if (count($errors) > 0) {
            return new JsonResponse(['message' => (string) $errors], 400);
        }

        return new JsonResponse(['message' => 'Settings updated succesfully!'], 201);
    }
}
