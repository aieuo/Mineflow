<?php

namespace aieuo\mineflow\exception;

use Throwable;

class UndefinedMineflowPropertyException extends \InvalidStateException {

    /* @var string */
    private $variableName;
    /** @var string */
    private $propertyName;

    public function __construct(string $variableName, string $propertyName, string $message = "", int $code = 0, Throwable $previous = null) {
        $this->variableName = $variableName;
        $this->propertyName = $propertyName;
        parent::__construct($message, $code, $previous);
    }

    public function getVariableName(): string {
        return $this->variableName;
    }

    public function getPropertyName(): string {
        return $this->propertyName;
    }
}