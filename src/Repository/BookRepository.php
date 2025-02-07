<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Book;
use App\Enum\Genre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Book>
 */
class BookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }

    public function findAllBooksPaginated(
        ?Genre $genre = null,
        int $page = 1,
        int $limit = 10
    ): array {
        $queryBuilder = $this->createQueryBuilder('b')
            ->orderBy('b.id', 'DESC');

        if ($genre !== null) {
            $queryBuilder
                ->andWhere('b.genre = :genre')
                ->setParameter('genre', $genre);
        }

        $firstResult = ($page - 1) * $limit;

        $queryBuilder
            ->setFirstResult($firstResult)
            ->setMaxResults($limit);

        return $queryBuilder->getQuery()->getArrayResult();
    }
}
