<?php

namespace App\Entity;

use App\Repository\ArticleRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null; //Identifiant unique auto-généré

    #[ORM\Column(length: 255, unique: true)]
    private ?string $title = null; //Libellé

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null; //description

     /**
     * @ORM\Column(type="datetime_immutable", options={"default": "CURRENT_TIMESTAMP"})
     */
    private ?\DateTimeInterface $created_date = null; //date de création

     /**
     * @ORM\Column(type="datetime_immutable", options={"default": "CURRENT_TIMESTAMP"})
     */
    private ?\DateTimeInterface $updated_date = null; //date de dernière modification
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
        return $this->created_date;
    }

    public function setUpdatedDate(\DateTimeInterface $updated_date): static
    {
        $this->updated_date = $updated_date;

        return $this;
    }
}
