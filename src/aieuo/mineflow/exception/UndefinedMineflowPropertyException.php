<?php

namespace aieuo\mineflow\exception;

use Throwable;

class UndefinedMineflowPropertyException extends MineflowException {

    private string $variableName;
    private string $propertyName;

    public function __construct(string $variableName, string $propertyName, string $message = null, int $code = 0, Throwable $previous = null) {
        $this->variableName = $variableName;
        $this->propertyName = $propertyName;
        parent::__construct($message ?? "§cUndefined index: ".$variableName.".§l".$propertyName."§r", $code, $previous);
    }

    public function getVariableName(): string {
        return $this->variableName;
    }

    public function getPropertyName(): string {
        return $this->propertyName;
    }
}