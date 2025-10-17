<?php

namespace App\Tests\Unit;

use App\Entity\Student;
use PHPUnit\Framework\TestCase;

class StudentTest extends TestCase
{
    public function testGetRoles(): void
    {
        // 1. მომზადება (Arrange)
        $student = new Student();

        // 2. მოქმედება (Act)
        $roles = $student->getRoles();

        // 3. მტკიცება (Assert)
        $this->assertIsArray($roles);
        $this->assertContains('ROLE_USER', $roles, "Student-ის როლებმა უნდა შეიცავდეს 'ROLE_USER'-ს");
    }
}
