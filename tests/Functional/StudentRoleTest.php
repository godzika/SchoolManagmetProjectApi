<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class StudentRoleTest extends WebTestCase
{
    public function testVerifiedStudentCanLogInAndSeeProfile(): void
    {
        $client = static::createClient();

        // 1. ვიღებთ ტოკენს დადასტურებული სტუდენტით
        $client->request(
            'POST',
            '/api/login_check',
            [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'student.verified@example.com',
                'password' => 'studentpass',
            ])
        );
        $this->assertResponseIsSuccessful('Verified student login should succeed.');
        $data = json_decode($client->getResponse()->getContent(), true);
        $token = $data['token'];

        // 2. ვამოწმებთ "ჩემი პროფილის" ენდფოინთს
        $client->request('GET', '/api/student/me', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token
        ]);
        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('student.verified@example.com', $client->getResponse()->getContent());
    }

    public function testUnverifiedStudentCannotLogIn(): void
    {
        $client = static::createClient();

        // ვცდილობთ ლოგინს დაუდასტურებელი სტუდენტით
        $client->request(
            'POST',
            '/api/login_check',
            [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'student.unverified@example.com',
                'password' => 'studentpass',
            ])
        );

        // ვამოწმებთ, რომ ლოგინი იბლოკება
        $this->assertResponseStatusCodeSame(401);
        $this->assertStringContainsString('pending admin approval', $client->getResponse()->getContent());
    }
}
