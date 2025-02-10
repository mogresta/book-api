<?php

namespace App\Tests\Functional\Controller;

use App\Entity\Book;
use App\Enum\Genre;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;

class BookControllerTest extends WebTestCase
{
    private $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get('doctrine')->getManager();
    }

    private function createTestBook(): Book
    {
        $book = new Book();
        $book->setIsbn('9780123456789')
            ->setTitle('Test Book')
            ->setAuthor('Test Author')
            ->setPublishedYear(2024)
            ->setGenre(Genre::FICTION);

        $this->entityManager->persist($book);
        $this->entityManager->flush();

        return $book;
    }

    public function testListBooks(): void
    {
        $this->createTestBook();

        $this->client->request('GET', '/api/books');

        $this->assertResponseIsSuccessful();
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('items', $response);
        $this->assertArrayHasKey('totalItems', $response);
        $this->assertArrayHasKey('page', $response);
        $this->assertArrayHasKey('limit', $response);
        $this->assertArrayHasKey('totalPages', $response);
        $this->assertCount(1, $response['items']);
    }

    public function testListBooksWithPagination(): void
    {
        $this->createTestBook();
        $this->createTestBook();

        $this->client->request('GET', '/api/books?page=1&limit=10');

        $this->assertResponseIsSuccessful();
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals(1, $response['page']);
        $this->assertEquals(10, $response['limit']);
        $this->assertEquals(2, $response['totalItems']);
        $this->assertEquals(1, $response['totalPages']);
        $this->assertCount(2, $response['items']);
    }

    public function testListBooksWithInvalidGenre(): void
    {
        $this->client->request('GET', '/api/books?genre=InvalidGenre');

        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('error', $response);
        $this->assertArrayHasKey('validGenres', $response);
    }

    public function testGetBook(): void
    {
        $book = $this->createTestBook();

        $this->client->request('GET', '/api/books/' . $book->getIsbn());

        $this->assertResponseIsSuccessful();
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals($book->getIsbn(), $response['isbn']);
        $this->assertEquals($book->getTitle(), $response['title']);
    }

    public function testGetNonExistentBook(): void
    {
        $this->client->request('GET', '/api/books/nonexistent');
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testCreateBook(): void
    {
        $bookData = [
            'isbn' => '9780123456789',
            'title' => 'New Book',
            'author' => 'New Author',
            'publishedYear' => 2024,
            'genre' => 'Fiction'
        ];

        $this->client->request(
            'POST',
            '/api/books',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($bookData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals($bookData['isbn'], $response['isbn']);
        $this->assertEquals($bookData['title'], $response['title']);
    }

    public function testCreateBookWithInvalidGenre(): void
    {
        $bookData = [
            'isbn' => '9780123456789',
            'title' => 'New Book',
            'author' => 'New Author',
            'publishedYear' => 2024,
            'genre' => 'InvalidGenre'
        ];

        $this->client->request(
            'POST',
            '/api/books',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($bookData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('error', $response);
        $this->assertArrayHasKey('validGenres', $response);
    }

    public function testUpdateBook(): void
    {
        $book = $this->createTestBook();

        $updateData = [
            'title' => 'Updated Title',
            'author' => 'Updated Author',
            'publishedYear' => 2025,
            'genre' => 'Science Fiction'
        ];

        $this->client->request(
            'PUT',
            '/api/books/' . $book->getIsbn(),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($updateData)
        );

        $this->assertResponseIsSuccessful();
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals($updateData['title'], $response['title']);
        $this->assertEquals($updateData['author'], $response['author']);
        $this->assertEquals($updateData['publishedYear'], $response['publishedYear']);
        $this->assertEquals($updateData['genre'], $response['genre']);
    }

    public function testUpdateNonExistentBook(): void
    {
        $updateData = [
            'title' => 'Updated Title',
            'author' => 'Updated Author',
            'publishedYear' => 2024,
            'genre' => 'Fiction'
        ];

        $this->client->request(
            'PUT',
            '/api/books/nonexistent',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($updateData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testDeleteBook(): void
    {
        $book = $this->createTestBook();

        $this->client->request('DELETE', '/api/books/' . $book->getIsbn());

        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $this->client->request('GET', '/api/books/' . $book->getIsbn());
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testDeleteNonExistentBook(): void
    {
        $this->client->request('DELETE', '/api/books/nonexistent');
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->entityManager->createQuery('DELETE FROM App\Entity\Book')->execute();
        $this->entityManager->close();
    }
}