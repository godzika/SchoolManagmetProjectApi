<?php

namespace App\Controller;

use App\Entity\Student;
use App\Repository\StudentRepository;
use Doctrine\ORM\EntityManagerInterface; // Import EntityManagerInterface
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request; // Import Request
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface; // Import ValidatorInterface

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
        // Use student:read group to avoid exposing sensitive data like password hash
        return $this->json($students, Response::HTTP_OK, [], ['groups' => 'student:read']);
    }

    /**
     * აბრუნებს მიმდინარე ავტორიზებული სტუდენტის პროფილს
     */
    #[Route('/student/me', name: 'api_student_profile_get', methods: ['GET'])] // Changed name slightly
    #[IsGranted('ROLE_USER')] // წვდომა აქვს მხოლოდ სტუდენტს
    public function getMyProfile(): Response
    {
        /** @var Student $student */
        $student = $this->getUser();
        // Use student:read group
        return $this->json($student, Response::HTTP_OK, [], ['groups' => 'student:read']);
    }

    /**
     * Allows the currently authenticated student to update their profile.
     */
    #[Route('/student/me', name: 'api_student_profile_update', methods: ['PUT', 'PATCH'])] // Added new route
    #[IsGranted('ROLE_USER')]
    public function updateMyProfile(
        Request $request,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ): Response {
        /** @var Student $student */
        $student = $this->getUser();
        $data = json_decode($request->getContent(), true);

        // Update fields if they exist in the request data
        if (isset($data['firstName'])) {
            $student->setFirstName($data['firstName']);
        }
        if (isset($data['lastName'])) {
            $student->setLastName($data['lastName']);
        }
        // Add more editable fields here in the future (e.g., dateOfBirth if allowed)
        // if (isset($data['dateOfBirth'])) {
        //     try {
        //         $student->setDateOfBirth(new \DateTimeImmutable($data['dateOfBirth']));
        //     } catch (\Exception $e) {
        //         return $this->json(['message' => 'Invalid date format for dateOfBirth.'], Response::HTTP_BAD_REQUEST);
        //     }
        // }

        // Validate the updated entity
        $errors = $validator->validate($student);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                // Construct message with property path for clarity
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        // Persist changes
        $em->flush();

        // Return updated profile using the same serialization group
        return $this->json($student, Response::HTTP_OK, [], ['groups' => 'student:read']);
    }


    /**
     * აბრუნებს მიმდინარე ავტორიზებული სტუდენტის ნიშნებს
     */
    #[Route('/student/me/grades', name: 'api_student_grades_get', methods: ['GET'])] // Changed name slightly
    #[IsGranted('ROLE_USER')] // წვდომა აქვს მხოლოდ სტუდენტს
    public function getMyGrades(): Response
    {
        /** @var Student $student */
        $student = $this->getUser();
        // Use student:read group which should include grades
        return $this->json($student->getGrades(), Response::HTTP_OK, [], ['groups' => 'student:read']);
    }
}
