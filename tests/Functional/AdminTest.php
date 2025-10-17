<?php

namespace App\Tests\Functional;

use App\Repository\AdminUserRepository;
use App\Repository\StudentRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminTest extends WebTestCase
{
    public function testAdminCanVerifyStudent(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        // ვიღებთ მომხმარებლებს Fixtures-დან, რომლებიც ტესტის დაწყებამდე იტვირთება
        $admin = $container->get(AdminUserRepository::class)->findOneBy(['email' => 'admin@example.com']);
        $student = $container->get(StudentRepository::class)->findOneBy(['email' => 'student.unverified@example.com']);

        // თუ ადმინი ვერ მოიძებნა, ტესტს ვაჩერებთ გარკვევითი შეტყობინებით
        $this->assertNotNull($admin, 'Admin user not found in test database. Did fixtures load correctly?');
        $this->assertNotNull($student, 'Unverified student not found in test database.');

        $this->assertFalse($student->isVerified());

        // ვავტორიზდებით ადმინით პირდაპირ, /api/login_check-ის გარეშე
        $client->loginUser($admin);

        // ვაგზავნით მოთხოვნას
        $client->request('PATCH', '/api/admin/students/' . $student->getId() . '/verify');

        // ვამოწმებთ შედეგს
        $this->assertResponseIsSuccessful();
    }
}
