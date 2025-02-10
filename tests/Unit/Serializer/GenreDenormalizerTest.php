<?php

declare(strict_types=1);

namespace App\Tests\Unit\Serializer;

use App\Enum\Genre;
use App\Exception\InvalidGenreException;
use App\Serializer\GenreDenormalizer;
use PHPUnit\Framework\TestCase;

class GenreDenormalizerTest extends TestCase
{
    private GenreDenormalizer $denormalizer;

    protected function setUp(): void
    {
        $this->denormalizer = new GenreDenormalizer();
    }

    public function testDenormalizeValidGenre(): void
    {
        $result = $this->denormalizer->denormalize('Fiction', Genre::class);

        $this->assertEquals(Genre::FICTION, $result);
    }

    public function testDenormalizeNull(): void
    {
        $result = $this->denormalizer->denormalize(null, Genre::class);

        $this->assertNull($result);
    }

    public function testDenormalizeInvalidGenreThrowsException(): void
    {
        $this->expectException(InvalidGenreException::class);

        $this->denormalizer->denormalize('Some Silly Genre', Genre::class);
    }

    public function testDenormalizeNonStringThrowsException(): void
    {
        $this->expectException(InvalidGenreException::class);

        $this->denormalizer->denormalize(123, Genre::class);
    }

    public function testSupportsDenormalization(): void
    {
        $this->assertTrue($this->denormalizer->supportsDenormalization(null, Genre::class));
        $this->assertFalse($this->denormalizer->supportsDenormalization(null, 'AuthorClass'));
    }
}
