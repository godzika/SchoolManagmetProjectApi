<?php

namespace App\Controller;

use App\Entity\Student;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegistrationController extends AbstractController
{
    /**
     * @throws \Exception
     */
    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ): Response {
        $data = json_decode($request->getContent(), true);

        if (empty($data['email']) || empty($data['password'])) {
            return $this->json(['message' => 'Email and password are required'], Response::HTTP_BAD_REQUEST);
        }

        $student = new Student();
        $student->setEmail($data['email']);
        $student->setFirstName($data['firstName'] ?? '');
        $student->setLastName($data['lastName'] ?? '');

        if (!empty($data['dateOfBirth'])) {
            $student->setDateOfBirth(new \DateTimeImmutable($data['dateOfBirth']));
        }

        $errors = $validator->validate($student);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $hashedPassword = $passwordHasher->hashPassword($student, $data['password']);
        $student->setPassword($hashedPassword);

        $em->persist($student);
        $em->flush();

        return $this->json(
            ['message' => 'Registration successful! Please wait for admin approval.'],
            Response::HTTP_CREATED
        );
    }
}
