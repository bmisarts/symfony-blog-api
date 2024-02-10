<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ArticleRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
#[UniqueEntity(fields: ['title','description'], ignoreNull: 'description')]
#[ORM\HasLifecycleCallbacks]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null; //Identifiant unique auto-généré

    #[ORM\Column(length: 255, unique: true)]  //Le titre "{{ value }}" a déjà été utilisé pour un autre arcticle.
    private ?string $title = null; //Libellé

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null; //description

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private $createdAt = null; //date de création

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private $updatedAt = null; //date de dernière modification
    
    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTime();
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getCreatedDate(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

}
