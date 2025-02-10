<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Service\BookService;
use PHPUnit\Framework\TestCase;
use App\Entity\Book;
use App\Enum\Genre;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;

class BookServiceTest extends TestCase
{
    private BookRepository $repository;
    private EntityManagerInterface $entityManager;
    private BookService $bookService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->createMock(BookRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->bookService = new BookService($this->repository, $this->entityManager);
    }

    public function testGetAllBooks(): void
    {
        $books = [
            $this->createMock(Book::class),
            $this->createMock(Book::class)
        ];

        $this->repository->expects($this->once())
            ->method('findAllBooksPaginated')
            ->with(null, 1, 10)
            ->willReturn($books);

        $result = $this->bookService->getAllBooks();

        $this->assertSame($books, $result['items']);
        $this->assertEquals(2, $result['totalItems']);
        $this->assertEquals(1, $result['page']);
        $this->assertEquals(10, $result['limit']);
        $this->assertEquals(1, $result['totalPages']);
    }

    public function testGetBookByIsbn(): void
    {
        $book = $this->createMock(Book::class);
        $isbn = '9780123456789';

        $this->repository->expects($this->once())
            ->method('findOneBy')
            ->with(['isbn' => $isbn])
            ->willReturn($book);

        $result = $this->bookService->getBookByIsbn($isbn);

        $this->assertSame($book, $result);
    }

    public function testCreateBook(): void
    {
        $book = $this->createMock(Book::class);

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($book);

        $this->entityManager->expects($this->once())
            ->method('flush');

        $result = $this->bookService->createBook($book);

        $this->assertSame($book, $result);
    }

    public function testUpdateBook(): void
    {
        $book = $this->createMock(Book::class);
        $updatedData = $this->createMock(Book::class);

        $updatedData->expects($this->once())
            ->method('getTitle')
            ->willReturn('Updated Title');

        $updatedData->expects($this->once())
            ->method('getAuthor')
            ->willReturn('Updated Author');

        $updatedData->expects($this->once())
            ->method('getPublishedYear')
            ->willReturn(2024);

        $updatedData->expects($this->once())
            ->method('getGenre')
            ->willReturn(Genre::FICTION);

        $book->expects($this->once())
            ->method('setTitle')
            ->with('Updated Title')
            ->willReturnSelf();

        $book->expects($this->once())
            ->method('setAuthor')
            ->with('Updated Author')
            ->willReturnSelf();

        $book->expects($this->once())
            ->method('setPublishedYear')
            ->with(2024)
            ->willReturnSelf();

        $book->expects($this->once())
            ->method('setGenre')
            ->with(Genre::FICTION)
            ->willReturnSelf();

        $this->entityManager->expects($this->once())
            ->method('flush');

        $result = $this->bookService->updateBook($book, $updatedData);

        $this->assertSame($book, $result);
    }

    public function testDeleteBook(): void
    {
        $book = $this->createMock(Book::class);

        $this->entityManager->expects($this->once())
            ->method('remove')
            ->with($book);

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->bookService->deleteBook($book);
    }
}
