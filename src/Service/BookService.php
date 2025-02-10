<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Book;
use App\Enum\Genre;
use App\Repository\BookRepository;
use App\Tests\Unit\Serializer\GenreDenormalizerTest;
use Doctrine\ORM\EntityManagerInterface;

/**  @see GenreDenormalizerTest */
class BookService
{
    public function __construct(
        private BookRepository $repository,
        private EntityManagerInterface $entityManager
    ) {}

    public function getAllBooks(
        ?Genre $genre = null,
        int $page = 1,
        int $limit = 10
    ): array {
        $books = $this->repository->findAllBooksPaginated($genre, $page, $limit);

        return [
            'items' => $books,
            'totalItems' => count($books),
            'page' => $page,
            'limit' => $limit,
            'totalPages' => ceil(count($books) / $limit)
        ];
    }

    public function getBookByIsbn(string $isbn): ?Book
    {
        return $this->repository->findOneBy(['isbn' => $isbn]);
    }

    public function createBook(Book $book): Book
    {
        $this->entityManager->persist($book);
        $this->entityManager->flush();

        return $book;
    }

    public function updateBook(Book $book, Book $updatedData): Book
    {
        $book->setTitle($updatedData->getTitle())
            ->setAuthor($updatedData->getAuthor())
            ->setPublishedYear($updatedData->getPublishedYear())
            ->setGenre($updatedData->getGenre());

        $this->entityManager->flush();

        return $book;
    }

    public function deleteBook(Book $book): void
    {
        $this->entityManager->remove($book);
        $this->entityManager->flush();
    }
}