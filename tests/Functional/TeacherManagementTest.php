<?php

namespace App\Tests\Functional;

use App\Repository\TeacherRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TeacherManagementTest extends WebTestCase
{
    public function testAdminCanCreateAndManageTeachers(): void
    {
        $client = static::createClient();

        // ადმინი იქმნება Fixtures-ის მიერ ტესტის დაწყებამდე
        $client->request(
            'POST',
            '/api/login_check',
            [], [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'admin@example.com',
                'password' => 'adminpass',
            ])
        );

        $this->assertResponseIsSuccessful('Admin login failed. This indicates a problem with database state during tests.');
        $loginData = json_decode($client->getResponse()->getContent(), true);
        $token = $loginData['token'];

        // მასწავლებლის შექმნა
        $client->request('POST', '/api/admin/teachers', [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_AUTHORIZATION' => 'Bearer ' . $token], json_encode(['email' => 'new.teacher@example.com', 'password' => 'teacherpass', 'firstName' => 'ახალი', 'lastName' => 'მასწავლებელი']));
        $this->assertResponseStatusCodeSame(201);
        // ... დანარჩენი ტესტი უცვლელია
    }

    public function testTeacherCannotCreateAnotherTeacher(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $teacher = $container->get(TeacherRepository::class)->findOneByEmail('teacher@example.com');
        $this->assertNotNull($teacher, 'Teacher not found in test database.');

        $client->loginUser($teacher);

        $client->request('POST', '/api/admin/teachers', [], [], ['CONTENT_TYPE' => 'application/json'], '{}');

        $this->assertResponseStatusCodeSame(403);
    }
}
