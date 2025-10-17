<?php

namespace App\Entity;

use App\Repository\SiteInfoRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: SiteInfoRepository::class)]
#[ORM\Table(name: 'site_info')]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['key'], message: 'This key already exists.')]
class SiteInfo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank]
    private ?string $key = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $value = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updated_at = null;

    public function __construct()
    {
        $this->created_at = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updated_at = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getKey(): ?string { return $this->key; }
    public function setKey(string $key): static { $this->key = $key; return $this; }
    public function getValue(): ?string { return $this->value; }
    public function setValue(?string $value): static { $this->value = $value; return $this; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->created_at; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updated_at; }
}
