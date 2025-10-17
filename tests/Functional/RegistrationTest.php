<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegistrationTest extends WebTestCase
{
    public function testStudentRegistrationSuccess(): void
    {
        // 1. ვქმნით ტესტ-კლიენტს, რომელიც გაგზავნის მოთხოვნას
        $client = static::createClient();

        // 2. ვაგზავნით POST მოთხოვნას /api/register მისამართზე
        $client->request(
            'POST',
            '/api/register',
            [], // query params
            [], // files
            ['CONTENT_TYPE' => 'application/json'], // headers
            json_encode([                         // body
                'email' => 'test.student@example.com',
                'password' => 'password123',
                'firstName' => 'სატესტო',
                'lastName' => 'სტუდენტი',
                'dateOfBirth' => '2005-02-02'
            ])
        );

        // 3. ვამოწმებთ პასუხს
        $this->assertResponseStatusCodeSame(201, 'რეგისტრაცია წარმატებით უნდა დასრულდეს');

        // ვიღებთ პასუხს და ვაქცევთ PHP მასივად
        $response = $client->getResponse();
        $responseData = json_decode($response->getContent(), true);

        // ვამოწმებთ, რომ მასივში არსებობს 'message' გასაღები
        $this->assertArrayHasKey('message', $responseData);
        // ვამოწმებთ, რომ ამ გასაღების მნიშვნელობა არის ის, რასაც ველით
        $this->assertSame('Registration successful! Please wait for admin approval.', $responseData['message']);
    }

    public function testRegistrationFailsWithMissingData(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'password' => 'password123',
            ])
        );

        $this->assertResponseStatusCodeSame(400, 'მოთხოვნა უნდა ჩავარდეს, თუ იმეილი არ არის მითითებული');
    }
}
