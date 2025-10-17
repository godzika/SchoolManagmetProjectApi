<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/teacher')]
#[IsGranted('ROLE_TEACHER')]
class TeacherController extends AbstractController
{
    #[Route('/me', name: 'api_teacher_profile', methods: ['GET'])]
    public function getMyProfile(): Response
    {
        // getUser() მეთოდი აბრუნებს მიმდინარე ავტორიზებულ მომხმარებელს (ამ შემთხვევაში Teacher ობიექტს)
        $teacher = $this->getUser();

        return $this->json($teacher, Response::HTTP_OK, [], ['groups' => 'teacher:read']);
    }
}
