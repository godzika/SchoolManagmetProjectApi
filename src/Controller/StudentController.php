<?php

namespace App\Controller;

use App\Entity\Student;
use App\Repository\StudentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api')] // მთავარი პრეფიქსი
class StudentController extends AbstractController
{
    /**
     * აბრუნებს ყველა სტუდენტის სიას (ხელმისაწვდომია მასწავლებლებისთვის)
     */
    #[Route('/students', name: 'api_students_list', methods: ['GET'])]
    #[IsGranted('ROLE_TEACHER')]
    public function getStudentList(StudentRepository $studentRepository): Response
    {
        $students = $studentRepository->findAll();
        return $this->json($students, Response::HTTP_OK, [], ['groups' => 'student:read']);
    }

    /**
     * აბრუნებს მიმდინარე ავტორიზებული სტუდენტის პროფილს
     */
    #[Route('/student/me', name: 'api_student_profile', methods: ['GET'])]
    #[IsGranted('ROLE_USER')] // წვდომა აქვს მხოლოდ სტუდენტს
    public function getMyProfile(): Response
    {
        /** @var Student $student */
        $student = $this->getUser();
        return $this->json($student, Response::HTTP_OK, [], ['groups' => 'student:read']);
    }

    /**
     * აბრუნებს მიმდინარე ავტორიზებული სტუდენტის ნიშნებს
     */
    #[Route('/student/me/grades', name: 'api_student_grades', methods: ['GET'])]
    #[IsGranted('ROLE_USER')] // წვდომა აქვს მხოლოდ სტუდენტს
    public function getMyGrades(): Response
    {
        /** @var Student $student */
        $student = $this->getUser();
        return $this->json($student->getGrades(), Response::HTTP_OK, [], ['groups' => 'student:read']);
    }
}
