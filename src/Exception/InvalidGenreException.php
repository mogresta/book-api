<?php

declare(strict_types=1);

namespace App\Exception;

use App\Enum\Genre;
use Exception;

class InvalidGenreException extends Exception
{
    private array $validGenres;

    public function __construct()
    {
        parent::__construct('Invalid genre value');
        $this->validGenres = Genre::cases();
    }

    public function getValidGenres(): array
    {
        return $this->validGenres;
    }
}