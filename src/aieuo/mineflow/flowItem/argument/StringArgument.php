<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\argument;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\Element;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;

class StringArgument extends FlowItemArgument {

    public function __construct(
        string         $name,
        private string $value = "",
        string         $description = "",
        private string $example = "",
        private bool   $optional = false,
    ) {
        parent::__construct($name, $description);
    }

    public function value(string $value): self {
        $this->value = $value;
        return $this;
    }

    public function getRawString(): string {
        return $this->value;
    }

    public function getString(FlowItemExecutor $executor): string {
        return $executor->replaceVariables($this->getRawString());
    }

    public function example(string $example): self {
        $this->example = $example;
        return $this;
    }

    public function getExample(): string {
        return $this->example;
    }

    public function optional(): static {
        $this->optional = true;
        return $this;
    }

    public function required(): static {
        $this->optional = false;
        return $this;
    }

    public function isOptional(): bool {
        return $this->optional;
    }

    public function isValid(): bool {
        return $this->isOptional() or $this->getRawString() !== "";
    }

    public function createFormElement(array $variables): Element {
        return new ExampleInput(
            $this->getDescription(),
            $this->getExample(),
            $this->getRawString(),
            required: !$this->isOptional()
        );
    }

    public function jsonSerialize(): string {
        return $this->getRawString();
    }

    public function load(mixed $value): void {
        $this->value($value);
    }

    public function __toString(): string {
        return $this->getRawString();
    }
}
