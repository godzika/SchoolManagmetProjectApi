<?php

namespace App\Tests\Functional;


use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TeacherRoleTest extends WebTestCase
{
    /**
     * დამხმარე მეთოდი, რომელიც იღებს კლიენტს და აბრუნებს მასწავლებლის ტოკენს.
     */
    private function getTeacherToken(KernelBrowser $client): string
    {
        $client->request(
            'POST',
            '/api/login_check',
            [], [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                // ჩვენი security.yaml-ის მიხედვით, ლოგინისთვის ვიყენებთ 'email' ველს
                'email' => 'teacher@example.com',
                'password' => 'teacherpass',
            ])
        );

        $this->assertResponseIsSuccessful('Teacher login failed. Check fixtures and security.yaml.');

        $data = json_decode($client->getResponse()->getContent(), true);

        return $data['token'];
    }

    /**
     * ტესტი ამოწმებს, შეუძლია თუ არა მასწავლებელს საკუთარი პროფილის ნახვა.
     */
    public function testTeacherCanViewOwnProfile(): void
    {
        $client = static::createClient();
        $token = $this->getTeacherToken($client);

        $client->request('GET', '/api/teacher/me', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token
        ]);

        $this->assertResponseIsSuccessful();
        $responseData = json_decode($client->getResponse()->getContent(), true);

        // ვამოწმებთ, რომ პასუხში დაბრუნებული იმეილი ნამდვილად ჩვენი მასწავლისაა
        $this->assertSame('teacher@example.com', $responseData['email']);
    }

    /**
     * ტესტი ამოწმებს, შეუძლია თუ არა მასწავლებელს სტუდენტების სიის ნახვა.
     */
    public function testTeacherCanViewStudentList(): void
    {
        $client = static::createClient();
        $token = $this->getTeacherToken($client);

        $client->request('GET', '/api/students', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token
        ]);

        $this->assertResponseIsSuccessful();

        // ვამოწმებთ, რომ პასუხი შეიცავს ჩვენს ერთ-ერთ სატესტო სტუდენტს fixtures-დან
        $this->assertStringContainsString('student.unverified@example.com', $client->getResponse()->getContent());
    }
}
