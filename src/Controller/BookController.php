<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Book;
use App\Enum\Genre;
use App\Exception\InvalidGenreException;
use App\Service\BookService;
use App\Validator\BookValidator;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;

/** Basic controller that uses serializer and validator components
 *  Of changes implemented and not specified, I added pagination
 *  Many improvements possible such as DTOs for requests and responses,
 *  Normalizers, more meaningful Exception classes and Validators,
 *  json class that handles requests, responses, error messages and pagination...
 */
#[Route('/api/books')]
class BookController extends AbstractController
{
    public function __construct(
        private BookService $bookService,
        private SerializerInterface $serializer,
        private BookValidator $validator,
        private readonly LoggerInterface $logger
    ) {
        $this->logger->error('CONSTRUCTOR TEST LOG');
    }

    #[Route('', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        try {
            /** Validation and sanitization of query parameters, pagination */
            $page = max(1, $request->query->getInt('page', 1));
            $limit = min(20, max(1, $request->query->getInt('limit', 10)));
            $genre = $request->query->get('genre');

            /** Optional: Validate genre if provided */
            if ($genre && !Genre::tryFrom($genre)) {
                throw new InvalidGenreException();
            }

            $books = $this->bookService->getAllBooks(
                genre: $genre ? Genre::from($genre) : null,
                page: $page,
                limit: $limit
            );

            return $this->json($books);
        } catch (InvalidGenreException $exception) {
            $this->logger->error('Genre Enum Error', [
                'error' => $exception->getMessage(),
            ]);

            return $this->json(
                [
                    'error' => $exception->getMessage(),
                    'validGenres' => $exception->getValidGenres()
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        } catch (Exception $exception) {
            $this->logger->error('Failed to fetch books', [
                'exception' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString()
            ]);

            return $this->json(
                ['error' => 'Failed to fetch books'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    #[Route('/{isbn}', methods: ['GET'])]
    public function get(string $isbn): JsonResponse
    {
        try {
            $book = $this->bookService->getBookByIsbn($isbn);
            if (!$book) {
                return $this->json(['error' => 'Book not found'], Response::HTTP_NOT_FOUND);
            }

            return $this->json($book);
        } catch (Exception $error) {
            $this->logger->error("Failed to fetch book {$isbn}", [
                'exception' => $error->getMessage(),
                'trace' => $error->getTraceAsString()
            ]);

            return $this->json(
                ['error' => "Failed to fetch book {$isbn}"],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $book = $this->serializer->deserialize($request->getContent(), Book::class, 'json');

            $this->validator->validate($book);

            $this->bookService->createBook($book);

            return $this->json($book, Response::HTTP_CREATED);
        } catch (InvalidGenreException $exception) {
            $this->logger->error('Genre Enum Error', [
                'error' => $exception->getMessage(),
            ]);

            return $this->json(
                [
                    'error' => $exception->getMessage(),
                    'validGenres' => $exception->getValidGenres()
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        } catch (Exception $error) {
            $this->logger->error("Failed to create book", [
                'exception' => $error->getMessage(),
                'trace' => $error->getTraceAsString()
            ]);

            return $this->json(
                ['error' => "Failed to create book"],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    #[Route('/{isbn}', methods: ['PUT'])]
    public function update(string $isbn, Request $request): JsonResponse
    {
        try {
            $book = $this->bookService->getBookByIsbn($isbn);
            if (!$book) {
                return $this->json(['error' => 'Book not found'], Response::HTTP_NOT_FOUND);
            }

            $updatedData = $this->serializer->deserialize($request->getContent(), Book::class, 'json');

            $this->validator->validate($updatedData);

            $this->bookService->updateBook($book, $updatedData);

            return $this->json($book);
        } catch (InvalidGenreException $exception) {
            /** This exception is thrown by the denormalizer */
            $this->logger->error('Genre Enum Error', [
                'error' => $exception->getMessage(),
            ]);

            return $this->json(
                [
                    'error' => $exception->getMessage(),
                    'validGenres' => $exception->getValidGenres()
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        } catch (Exception $error) {
            $this->logger->error("Failed to update book {$isbn}", [
                'exception' => $error->getMessage(),
                'trace' => $error->getTraceAsString()
            ]);

            return $this->json(
                ['error' => "Failed to update book {$isbn}"],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    #[Route('/{isbn}', methods: ['DELETE'])]
    public function delete(string $isbn): JsonResponse
    {
        try {
            $book = $this->bookService->getBookByIsbn($isbn);
            if (!$book) {
                return $this->json(['error' => 'Book not found'], Response::HTTP_NOT_FOUND);
            }

            $this->bookService->deleteBook($book);

            return $this->json(null, Response::HTTP_NO_CONTENT);
        } catch (Exception $error) {
            $this->logger->error("Failed to delete book {$isbn}", [
                'exception' => $error->getMessage(),
                'trace' => $error->getTraceAsString()
            ]);

            return $this->json(
                ['error' => "Failed to delete book {$isbn}"],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
