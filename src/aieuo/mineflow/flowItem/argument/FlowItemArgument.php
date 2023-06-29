<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\argument;

use aieuo\mineflow\formAPI\element\Element;
use aieuo\mineflow\variable\Variable;

abstract class FlowItemArgument {

    public function __construct(
        private readonly string $name,
        private mixed           $value = null,
        private string          $description = "",
        private readonly bool   $optional = false,
    ) {
    }

    public function getName(): string {
        return $this->name;
    }

    public function set(mixed $value): void {
        $this->value = $value;
    }

    public function get(): mixed {
        return $this->value;
    }

    public function setDescription(string $description): void {
        $this->description = $description;
    }

    public function getDescription(): string {
        return $this->description;
    }

    public function isOptional(): bool {
        return $this->optional;
    }

    public function isEmpty(): bool {
        return $this->value === null;
    }

    public function isNotEmpty(): bool {
        return $this->value !== null;
    }

    /**
     * @param array<string, Variable> $variables
     * @return Element
     */
    abstract public function createFormElement(array $variables): Element;

    public function __toString(): string {
        return (string)$this->get();
    }
}
