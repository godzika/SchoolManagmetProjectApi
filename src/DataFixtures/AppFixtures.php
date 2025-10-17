<?php

namespace App\DataFixtures;

use App\Entity\AdminUser;
use App\Entity\Student;
use App\Entity\Teacher;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // 1. ვქმნით ადმინს
        $admin = new AdminUser();
        $admin->setEmail('admin@example.com');
        $admin->setUsername('admin');
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'adminpass'));
        $admin->setRoles(['ROLE_ADMIN']);
        $manager->persist($admin);

        // 2. ვქმნით მასწავლებელს
        $teacher = new Teacher();
        $teacher->setEmail('teacher@example.com');
        $teacher->setFirstName('სატესტო');
        $teacher->setLastName('მასწავლებელი');
        $teacher->setPassword($this->passwordHasher->hashPassword($teacher, 'teacherpass'));
        $teacher->setRoles(['ROLE_TEACHER']);
        $manager->persist($teacher);

        // 3. ვქმნით დაუდასტურებელ სტუდენტს
        $unverifiedStudent = new Student();
        $unverifiedStudent->setEmail('student.unverified@example.com');
        $unverifiedStudent->setFirstName('დაუდასტურებელი');
        $unverifiedStudent->setLastName('სტუდენტი');
        $unverifiedStudent->setDateOfBirth(new \DateTimeImmutable('2005-01-01'));
        $unverifiedStudent->setPassword($this->passwordHasher->hashPassword($unverifiedStudent, 'studentpass'));
        $manager->persist($unverifiedStudent);

        // 4. ვქმნით დადასტურებულ სტუდენტს
        $verifiedStudent = new Student();
        $verifiedStudent->setEmail('student.verified@example.com');
        $verifiedStudent->setFirstName('დადასტურებული');
        $verifiedStudent->setLastName('სტუდენტი');
        $verifiedStudent->setDateOfBirth(new \DateTimeImmutable('2005-02-02'));
        $verifiedStudent->setPassword($this->passwordHasher->hashPassword($verifiedStudent, 'studentpass'));
        $verifiedStudent->setIsVerified(true);
        $manager->persist($verifiedStudent);

        $manager->flush();
    }
}
