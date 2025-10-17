<?php

namespace App\Controller;

use App\Entity\Grade;
use App\Entity\Student;
use App\Entity\Subject;
use App\Entity\Teacher;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api')]
#[IsGranted('ROLE_TEACHER')] // მთლიანი კონტროლერი დაცულია, წვდომა აქვს მინიმუმ მასწავლებელს
class GradeController extends AbstractController
{
    #[Route('/grades', name: 'api_grade_create', methods: ['POST'])]
    public function createGrade(
        Request $request,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ): Response {
        /** @var Teacher $teacher */
        $teacher = $this->getUser(); // ვიღებთ ავტორიზებულ მომხმარებელს (მასწავლებელს)

        $data = json_decode($request->getContent(), true);

        $student = $em->getRepository(Student::class)->find($data['studentId'] ?? 0);
        $subject = $em->getRepository(Subject::class)->find($data['subjectId'] ?? 0);

        if (!$student) {
            return $this->json(['message' => 'Student not found'], Response::HTTP_NOT_FOUND);
        }
        if (!$subject) {
            return $this->json(['message' => 'Subject not found'], Response::HTTP_NOT_FOUND);
        }

        $grade = new Grade();
        $grade->setScore($data['score'] ?? null);
        $grade->setStudent($student);
        $grade->setSubject($subject);
        $grade->setTeacher($teacher); // ნიშნის ავტორია მიმდინარე მასწავლებელი

        $errors = $validator->validate($grade);
        if (count($errors) > 0) {
            // ... ვალიდაციის შეცდომების დამუშავება
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $em->persist($grade);
        $em->flush();

        // ჯერჯერობით ვბრუნებთ მარტივ შეტყობინებას
        return $this->json(['message' => 'Grade created successfully', 'gradeId' => $grade->getId()], Response::HTTP_CREATED);
    }

    #[Route('/students/{id}/grades', name: 'api_student_grades', methods: ['GET'])]
    public function getStudentGrades(Student $student): Response
    {
        // $student ობიექტს Symfony თავად იპოვის {id}-ით
        // ვიყენებთ 'student:read' ჯგუფს, რათა ვაჩვენოთ სტუდენტის ნიშნები
        return $this->json($student, Response::HTTP_OK, [], ['groups' => 'student:read']);
    }
}
