<?php

declare(strict_types=1);

namespace App\Serializer;

use App\Enum\Genre;
use App\Exception\InvalidGenreException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/** Denormalizer for the genre to catch denormalization errors when genre that is not allowed is sent in request.
 *  Allows for null.
 */
class GenreDenormalizer implements DenormalizerInterface
{
    /** @throws InvalidGenreException */
    public function denormalize(mixed $data, string $type, string $format = null, array $context = []): ?Genre
    {
        if ($data === null) {
            return null;
        }

        if (!is_string($data)) {
            throw new InvalidGenreException();
        }

        $genre = Genre::tryFrom($data);
        if ($genre === null) {
            throw new InvalidGenreException();
        }

        return $genre;
    }

    public function supportsDenormalization(mixed $data, string $type, string $format = null, array $context = []): bool
    {
        return $type === Genre::class;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Genre::class => true
        ];
    }
}