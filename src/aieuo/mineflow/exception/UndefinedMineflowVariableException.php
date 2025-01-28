<?php

namespace aieuo\mineflow\exception;

use Throwable;

class UndefinedMineflowVariableException extends MineflowException {

    private string $variableName;

    public function __construct(string $variableName, string $message = null, int $code = 0, Throwable $previous = null) {
        $this->variableName = $variableName;
        parent::__construct($message ?? "§cUndefined variable: ".$variableName."§r", $code, $previous);
    }

    public function getVariableName(): string {
        return $this->variableName;
    }
}