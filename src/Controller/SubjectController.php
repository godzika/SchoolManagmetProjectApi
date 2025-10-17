<?php

namespace App\Controller;

use App\Repository\SubjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/subjects')]
#[IsGranted('IS_AUTHENTICATED_FULLY')] // წვდომა აქვს ყველას, ვინც ავტორიზებულია
class SubjectController extends AbstractController
{
    #[Route('', name: 'api_subjects_list', methods: ['GET'])]
    public function getSubjects(SubjectRepository $subjectRepository): Response
    {
        $subjects = $subjectRepository->findAll();

        // ჯერჯერობით არ ვამატებთ ჯგუფებს, რადგან Subject entity-ს რთული კავშირები არ აქვს
        return $this->json($subjects, Response::HTTP_OK);
    }
}
