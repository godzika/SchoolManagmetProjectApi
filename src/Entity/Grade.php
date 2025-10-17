<?php

namespace App\Entity;

use App\repository\GradeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;


#[ORM\Entity(repositoryClass: GradeRepository::class)]
#[ORM\Table(name: 'grades')]
#[ORM\HasLifecycleCallbacks] // რათა created_at ავტომატურად შეივსოს
class Grade
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['grade:read', 'student:read'])] // დაამატეთ ეს
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'Score cannot be empty.')]
    #[Assert\Range(
        notInRangeMessage: 'Score must be between {{ min }} and {{ max }}.',
        min: 1,
        max: 10,
    )]
    #[Groups(['grade:read', 'student:read'])] // დაამატეთ ეს
    private ?int $score = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\ManyToOne(targetEntity: Student::class, inversedBy: 'grades')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Grade must be assigned to a student.')]
    #[Groups(['grade:read'])] // დაამატეთ ეს
    private ?Student $student = null;

    #[ORM\ManyToOne(targetEntity: Subject::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Grade must be for a specific subject.')]
    #[Groups(['grade:read', 'student:read'])] // დაამატეთ ეს
    private ?Subject $subject = null;

    #[ORM\ManyToOne(targetEntity: Teacher::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Grade must be assigned by a teacher.')]
    #[Groups(['grade:read', 'student:read'])] // დაამატეთ ეს
    private ?Teacher $teacher = null;

    public function __construct()
    {
        $this->created_at = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function setScore(int $score): static
    {
        $this->score = $score;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    // setCreatedAt აღარ არის საჭირო, რადგან კონსტრუქტორი ართმევს თავს ამ საქმეს.

    public function getStudent(): ?Student
    {
        return $this->student;
    }

    public function setStudent(?Student $student): static
    {
        $this->student = $student;

        return $this;
    }

    public function getSubject(): ?Subject
    {
        return $this->subject;
    }

    public function setSubject(?Subject $subject): static
    {
        $this->subject = $subject;

        return $this;
    }

    public function getTeacher(): ?Teacher
    {
        return $this->teacher;
    }

    public function setTeacher(?Teacher $teacher): static
    {
        $this->teacher = $teacher;

        return $this;
    }
}
