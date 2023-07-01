<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\argument;

use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\Element;

abstract class FlowItemArgument {

    public function __construct(
        private readonly string $name,
        private mixed           $value = null,
        private string          $description = "",
        private bool            $optional = false,
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

    public function optional(): self {
        $this->optional = true;
        return $this;
    }

    public function required(): self {
        $this->optional = false;
        return $this;
    }

    public function isOptional(): bool {
        return $this->optional;
    }

    public function isEmpty(): bool {
        return $this->value === null;
    }

    public function isValid(): bool {
        return $this->isOptional() or !$this->isEmpty();
    }

    abstract public function createFormElement(array $variables): Element;

    public function buildEditPage(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->element($this->createFormElement($variables));
    }

    public function __toString(): string {
        return (string)$this->get();
    }
}
