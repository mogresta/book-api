<?php

declare(strict_types=1);

namespace App\Enum;

/** Not an exhaustive list, just for fun */
enum Genre: string
{
    case FICTION = 'Fiction';
    case NON_FICTION = 'Non-Fiction';
    case SCIENCE_FICTION = 'Science Fiction';
    case MYSTERY = 'Mystery';
    case ROMANCE = 'Romance';
    case FANTASY = 'Fantasy';
    case THRILLER = 'Thriller';
    case HISTORICAL_FICTION = 'Historical Fiction';
    case BIOGRAPHY = 'Biography';
    case SELF_HELP = 'Self-Help';
}