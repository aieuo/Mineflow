<?php
declare(strict_types=1);

namespace aieuo\mineflow\exception;

use Throwable;

class InvalidFlowValueException extends MineflowException {
    private string $flowItemName;

    public function __construct(string $flowItemName = "", string $message = "", int $code = 0, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
        $this->flowItemName = $flowItemName;
    }

    public function getFlowItemName(): string {
        return $this->flowItemName;
    }
}