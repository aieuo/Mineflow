<?php
declare(strict_types=1);

namespace aieuo\mineflow\exception;

use aieuo\mineflow\utils\Language;
use Throwable;

class InvalidFlowValueException extends \RuntimeException {
    /** @var string */
    private $flowItemName;

    public function __construct(string $flowItemName = "", string $message = "", int $code = 0, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
        $this->flowItemName = $flowItemName;
    }

    public function getFlowItemName(): string {
        return $this->flowItemName;
    }
}