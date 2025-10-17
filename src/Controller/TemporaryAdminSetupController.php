<?php

namespace App\Controller;

use App\Entity\AdminUser;
use App\Repository\AdminUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class TemporaryAdminSetupController extends AbstractController
{
    #[Route('/setup/create-super-admin', name: 'setup_create_admin', methods: ['POST'])]
    public function createAdmin(
        AdminUserRepository $adminRepository,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {
        // ვამოწმებთ, ხომ არ არსებობს უკვე ადმინი, რომ დუბლიკატი არ შევქმნათ
        $existingAdmin = $adminRepository->findOneBy(['email' => 'admin@example.com']);

        if ($existingAdmin) {
            return $this->json(['message' => 'Admin user already exists.'], Response::HTTP_CONFLICT);
        }

        // ვქმნით ახალ ადმინს
        $admin = new AdminUser();
        $admin->setEmail('admin@example.com');
        $admin->setUsername('admin');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($passwordHasher->hashPassword($admin, 'adminpass'));

        $em->persist($admin);
        $em->flush();

        return $this->json(['message' => 'Admin user created successfully! You can now log in.'], Response::HTTP_CREATED);
    }
}
