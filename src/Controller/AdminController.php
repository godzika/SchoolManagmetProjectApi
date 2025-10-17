<?php

namespace App\Controller;

use App\Entity\Student;
use App\Entity\Subject;
use App\Entity\Teacher;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Id;
use Exception;
use PHPUnit\Util\Json;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 *
 */
#[Route('/api/admin')]
#[IsGranted('ROLE_ADMIN')] // მთლიანი კონტროლერი დაცულია ადმინის როლით
class AdminController extends AbstractController
{
    // ===================================================================
    // სტუდენტების მართვა
    // ===================================================================

    /**
     * @param EntityManagerInterface $em
     * @return Response
     */
    #[Route('/students/', name: 'api_admin_get_students', methods: ['GET'])]
    public function getStudents(EntityManagerInterface $em): Response {
        $stundets = $em->getRepository(Student::class)->findAll();
        if(empty($stundets)) {
            return $this->json(["message"=>"No Stundets registred"], Response::HTTP_NOT_FOUND);
        }
        return $this->json($stundets, Response::HTTP_OK);
    }

    /**
     * @param EntityManagerInterface $em
     * @return Response
     */
    #[Route('/students/{id}/', name: 'api', methods: ['GET'])]
    public function getStundetById(EntityManagerInterface $em, $id): Response {
        $stundet = $em->getRepository(Student::class)->find($id);
        if(empty($stundet)) {
            return $this->json(["message"=>"No Stundets registred"], Response::HTTP_NOT_FOUND);
        }
        return $this->json($stundet, Response::HTTP_OK);
    }


    /**
     * @param EntityManagerInterface $em
     * @param int $id
     * @return Response
     */
    #[Route('/students/{id}/verify', name: 'api_admin_verify_student', methods: ['PATCH'])]
    public function verifyStudent(EntityManagerInterface $em, int $id): Response
    {
        $student = $em->getRepository(Student::class)->find($id);
        if(empty($student)) {
            return $this->json(["message"=>"No Stundets registred"], Response::HTTP_NOT_FOUND);
        }

        if ($student->isVerified()) {
            return $this->json(['message' => 'Student is already verified.'], Response::HTTP_OK);
        }

        $student->setIsVerified(true);
        $em->flush();

        return $this->json(['message' => 'Student has been successfully verified.'], Response::HTTP_OK);
    }

    /**
     * @param EntityManagerInterface $em
     * @return Response
     */
    #[Route('/students/verify/all', name: 'api_admin_verify_all_students', methods: ['PATCH'])]
    public function verifyAllStudents(EntityManagerInterface $em): Response
    {
        $unverifiedStudents = $em->getRepository(Student::class)->findBy(['isVerified' => false]);

        if (empty($unverifiedStudents)) {
            return $this->json(['message' => 'No unverified students found.'], Response::HTTP_OK);
        }

        foreach ($unverifiedStudents as $student) {
            $student->setIsVerified(true);
        }
        $em->flush();

        return $this->json(['message' => 'All unverified students have been successfully verified.'], Response::HTTP_OK);
    }

    // ===================================================================
    // მასწავლებლების მართვა (CRUD)
    // ===================================================================

    /**
     * ახალი მასწავლებლის შექმნა
     */
    #[Route('/teachers', name: 'api_admin_create_teacher', methods: ['POST'])]
    public function createTeacher(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        ValidatorInterface $validator
    ): Response {
        $data = json_decode($request->getContent(), true);

        $teacher = new Teacher();
        $teacher->setEmail($data['email'] ?? null);
        $teacher->setFirstName($data['firstName'] ?? null);
        $teacher->setLastName($data['lastName'] ?? null);
        $teacher->setPassword($passwordHasher->hashPassword($teacher, $data['password'] ?? ''));

        $errors = $validator->validate($teacher);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $em->persist($teacher);
        $em->flush();

        return $this->json($teacher, Response::HTTP_CREATED, [], ['groups' => 'teacher:read']);
    }

    /**
     * ყველა მასწავლებლის სიის წამოღება
     */
    #[Route('/teachers', name: 'api_admin_list_teachers', methods: ['GET'])]
    public function getTeachers(EntityManagerInterface $em): Response
    {
        $teachers = $em->getRepository(Teacher::class)->findAll();

        return $this->json($teachers, Response::HTTP_OK, [], ['groups' => 'teacher:read']);
    }

    /**
     * მასწავლებლის მონაცემების განახლება
     */
    #[Route('/teachers/{id}', name: 'api_admin_update_teacher', methods: ['PUT'])]
    public function updateTeacher(Teacher $teacher, Request $request, EntityManagerInterface $em): Response
    {
        $data = json_decode($request->getContent(), true);

        $teacher->setFirstName($data['firstName'] ?? $teacher->getFirstName());
        $teacher->setLastName($data['lastName'] ?? $teacher->getLastName());

        $em->flush();

        return $this->json($teacher, Response::HTTP_OK, [], ['groups' => 'teacher:read']);
    }

    /**
     * მასწავლებლის წაშლა
     */
    #[Route('/teachers/{id}', name: 'api_admin_delete_teacher', methods: ['DELETE'])]
    public function deleteTeacher(Teacher $teacher, EntityManagerInterface $em): Response
    {
        $em->remove($teacher);
        $em->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * მასწავლებელზე საგნის მიბმა
     */
    #[Route('/teachers/{teacherId}/subjects', name: 'api_admin_assign_subject', methods: ['POST'])]
    public function assignSubjectToTeacher(
        int $teacherId,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $teacher = $em->getRepository(Teacher::class)->find($teacherId);
        if (!$teacher) {
            return $this->json(['message' => 'Teacher not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        $subjectId = $data['subjectId'] ?? 0;

        $subject = $em->getRepository(Subject::class)->find($subjectId);
        if (!$subject) {
            return $this->json(['message' => 'Subject not found'], Response::HTTP_NOT_FOUND);
        }

        $teacher->addSubject($subject);
        $em->flush();

        return $this->json(
            ['message' => 'Subject assigned to teacher successfully'],
            Response::HTTP_OK
        );
    }

    // ===================================================================
    // საგნების მართვა
    // ===================================================================

    /**
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return Response
     */
    #[Route('/subjects', name: 'api_admin_get_subjects', methods: ['GET'])]
    public function getSubject(Request $request, EntityManagerInterface $em): Response
    {
        $subjects = $em->getRepository(Subject::class)->findAll();
        if (empty($subjects)) {
            return $this->json(['message' => 'No subjects found'], Response::HTTP_NOT_FOUND);
        }
        try {
            return $this->json($subjects, Response::HTTP_OK);
        } catch (Exception $e) {
            return $this->json(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
        }
    }

    /**
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param ValidatorInterface $validator
     * @return Response
     */
    #[Route('/subjects', name: 'api_admin_create_subject', methods: ['POST'])]
    public function createSubject(Request $request, EntityManagerInterface $em, ValidatorInterface $validator): Response
    {
        $data = json_decode($request->getContent(), true);
        $subject = new Subject();
        $subject->setName($data['name'] ?? null);
        $subject->setDescription($data['description'] ?? null);

        $errors = $validator->validate($subject);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $em->persist($subject);
        $em->flush();
        return $this->json($subject, Response::HTTP_CREATED);
    }

    /**
     * @param Subject $subject
     * @param EntityManagerInterface $em
     * @return Response
     */
    #[Route('/subjects/{id}', name: 'api_admin_delete_subject', methods: ['DELETE'])]
    public function deleteSubject(Subject $subject, EntityManagerInterface $em): Response
    {
        $em->remove($subject);
        $em->flush();

        return $this->json(['message' => 'Subject deleted successfully'], Response::HTTP_OK);
    }
}
