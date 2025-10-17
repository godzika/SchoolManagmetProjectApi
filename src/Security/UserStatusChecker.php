<?php

namespace App\Security;

use App\Entity\Student;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserStatusChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        // თუ მომხმარებელი არ არის Student-ის ობიექტი, არაფერს ვაკეთებთ
        if (!$user instanceof Student) {
            return;
        }

        // თუ მომხმარებელი არის სტუდენტი, მაგრამ არ არის დადასტურებული
        if (!$user->isVerified()) {
            // ვაგდებთ შეცდომას, რომელსაც მომხმარებელი დაინახავს
            throw new CustomUserMessageAuthenticationException(
                'Your account is pending admin approval. Please contact the administration.'
            );
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        // ეს მეთოდი არ გვჭირდება, მაგრამ ინტერფეისის გამო აუცილებელია არსებობდეს
    }
}
