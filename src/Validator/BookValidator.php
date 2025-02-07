<?php

declare(strict_types=1);

namespace App\Validator;

use App\Entity\Book;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/** This class does not have much sense in this context, however we are separating validation from the controller
 *  as down the line this is where any additional business validation logic should be implemented
 */
class BookValidator
{
    public function __construct(
        private ValidatorInterface $validator,
    ) {}

    public function validate(Book $book): void
    {
        $this->validator->validate($book);
    }
}