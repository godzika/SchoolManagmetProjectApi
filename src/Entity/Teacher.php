<?php

namespace App\Entity;

use App\Repository\TeacherRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TeacherRepository::class)]
#[ORM\Table(name: 'teachers')]
#[ORM\HasLifecycleCallbacks]
#[ORM\UniqueConstraint(name: 'UNIQ_TEACHER_EMAIL', fields: ['email'])]
class Teacher implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['teacher:read', 'grade:read', 'student:read'])] // დაემატა teacher:read
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Email]
    #[Groups(['teacher:read'])] // დაემატა
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null; // ამ ველს არ ვამატებთ ჯგუფში

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2)]
    #[Groups(['teacher:read', 'grade:read', 'student:read'])] // დაემატა teacher:read
    private ?string $first_name = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2)]
    #[Groups(['teacher:read', 'grade:read', 'student:read'])] // დაემატა teacher:read
    private ?string $last_name = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['teacher:read'])] // დაემატა
    private ?string $phone_number = null;

    #[ORM\Column]
    #[Groups(['teacher:read'])] // დაემატა
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['teacher:read'])] // დაემატა
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\OneToMany(targetEntity: Grade::class, mappedBy: 'teacher')]
    private Collection $grades; // ამ კავშირს არ ვსერიალიზებთ მასწავლებლის სიაში

    #[ORM\ManyToMany(targetEntity: Subject::class, inversedBy: 'teachers')]
    #[Groups(['teacher:read'])]
    private Collection $subjects;

    public function __construct()
    {
        $this->created_at = new \DateTimeImmutable();
        $this->grades = new ArrayCollection();
        $this->subjects = new ArrayCollection();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updated_at = new \DateTimeImmutable();
    }

    // --- დანარჩენი კოდი უცვლელია ---
    public function getId(): ?int { return $this->id; }
    public function getEmail(): ?string { return $this->email; }
    public function setEmail(string $email): static { $this->email = $email; return $this; }
    public function getUserIdentifier(): string { return (string) $this->email; }
    public function getRoles(): array { $roles = $this->roles; $roles[] = 'ROLE_TEACHER'; return array_unique($roles); }
    public function setRoles(array $roles): static { $this->roles = $roles; return $this; }
    public function getPassword(): ?string { return $this->password; }
    public function setPassword(string $password): static { $this->password = $password; return $this; }
    public function eraseCredentials(): void { }
    public function getFirstName(): ?string { return $this->first_name; }
    public function setFirstName(string $first_name): static { $this->first_name = $first_name; return $this; }
    public function getLastName(): ?string { return $this->last_name; }
    public function setLastName(string $last_name): static { $this->last_name = $last_name; return $this; }
    public function getPhoneNumber(): ?string { return $this->phone_number; }
    public function setPhoneNumber(?string $phone_number): static { $this->phone_number = $phone_number; return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->created_at; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updated_at; }
    public function getGrades(): Collection { return $this->grades; }
    public function addGrade(Grade $grade): static { if (!$this->grades->contains($grade)) { $this->grades->add($grade); $grade->setTeacher($this); } return $this; }
    public function removeGrade(Grade $grade): static { if ($this->grades->removeElement($grade)) { if ($grade->getTeacher() === $this) { $grade->setTeacher(null); } } return $this; }
    public function getSubjects(): Collection { return $this->subjects; }
    public function addSubject(Subject $subject): static { if (!$this->subjects->contains($subject)) { $this->subjects->add($subject); } return $this; }
    public function removeSubject(Subject $subject): static { $this->subjects->removeElement($subject); return $this; }
}
