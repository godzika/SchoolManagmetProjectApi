<?php

namespace App\Controller;

use App\Repository\AdminUserRepository;
use App\Repository\StudentRepository;
use App\Repository\TeacherRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class AuthenticationController extends AbstractController
{
    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(
        Request $request,
        AdminUserRepository $adminRepository,
        TeacherRepository $teacherRepository,
        StudentRepository $studentRepository,
        UserPasswordHasherInterface $passwordHasher,
        JWTTokenManagerInterface $jwtManager,
        UserCheckerInterface $userChecker
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null; // <--- ველი უნდა იყოს "email"
        $password = $data['password'] ?? null;

        if ($email === null || $password === null) {
            return $this->json(['message' => 'Email and password are required'], Response::HTTP_BAD_REQUEST);
        }

        /** @var UserInterface|null $user */
        // ვეძებთ სამივე ტიპის მომხმარებელს იმეილით
        $user = $adminRepository->findOneBy(['email' => $email]);
        if (!$user) {
            $user = $teacherRepository->findOneBy(['email' => $email]);
        }
        if (!$user) {
            $user = $studentRepository->findOneBy(['email' => $email]);
        }

        if (!$user || !$passwordHasher->isPasswordValid($user, $password)) {
            return $this->json(['code' => 401, 'message' => 'Invalid credentials.'], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $userChecker->checkPreAuth($user);
        } catch (\Exception $e) {
            return $this->json(['code' => 401, 'message' => $e->getMessage()], Response::HTTP_UNAUTHORIZED);
        }

        $token = $jwtManager->create($user);

        return $this->json(['token' => $token]);
    }
}
