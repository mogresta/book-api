<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\Genre;
use App\Repository\BookRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use DateTimeImmutable;

/** As other entities were not specified, I created a very simple Book Entity.
 *  Possible improvements include separate table for authors, ManyToOne relationship
 *  More detailed ISBN validation via a validator class
 *  Genre as ManyToOne instead of just enum
 *  Soft delete
 *
 *  The only changes implemented and not specified is createdAt and updatedAt fields,
 *  with updatedAt updated via lifecycle callback, as well as Genre Enum
 */
#[ORM\Entity(repositoryClass: BookRepository::class)]
class Book
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 13)]
    #[Assert\NotBlank]
    #[Assert\Length(exactly: 13)]
    private ?string $isbn = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $author = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Range(min: 1000, max: 9999)]
    private ?int $publishedYear = null;

    #[ORM\Column(length: 255, nullable: true, enumType: Genre::class)]
    private ?Genre $genre = null;

    /** added createdAt */
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private readonly DateTimeImmutable $createdAt;

    /** added updated at by lifecycle callback */
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIsbn(): ?string
    {
        return $this->isbn;
    }

    public function setIsbn(string $isbn): static
    {
        $this->isbn = $isbn;

        return $this;
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

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(string $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function getPublishedYear(): ?int
    {
        return $this->publishedYear;
    }

    public function setPublishedYear(?int $publishedYear): static
    {
        $this->publishedYear = $publishedYear;

        return $this;
    }

    public function getGenre(): ?Genre
    {
        return $this->genre;
    }

    public function setGenre(?Genre $genre): static
    {
        $this->genre = $genre;

        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    #[ORM\PreUpdate]
    public function setUpdatedAt(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
