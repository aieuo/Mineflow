<?php
declare(strict_types=1);

namespace aieuo\mineflow\exception;

use Throwable;

class MineflowMethodErrorException extends MineflowException {

    private string $variableName;
    private string $methodName;

    public function __construct(string $variableName, string $methodName, string $message = null, int $code = 0, Throwable $previous = null) {
        $this->variableName = $variableName;
        $this->methodName = $methodName;
        parent::__construct($message, $code, $previous);
    }

    public function getVariableName(): string {
        return $this->variableName;
    }

    public function getMethodName(): string {
        return $this->methodName;
    }
}