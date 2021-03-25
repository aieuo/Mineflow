<?php
declare(strict_types=1);

namespace aieuo\mineflow\exception;

use Throwable;

class InvalidFormValueException extends \RuntimeException {

    /* @var string */
    private $errorMessage;
    /* @var int */
    private $index;

    public function __construct(string $errorMessage, int $index, string $message = "", int $code = 0, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
        $this->errorMessage = $errorMessage;
        $this->index = $index;
    }

    public function getErrorMessage(): string {
        return $this->errorMessage;
    }

    public function getIndex(): int {
        return $this->index;
    }
}