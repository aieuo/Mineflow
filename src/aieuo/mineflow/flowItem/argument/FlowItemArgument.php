<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\argument;

abstract class FlowItemArgument implements \JsonSerializable {

    public function __construct(
        private readonly string $name,
        private string          $description = "",
    ) {
    }

    public function getName(): string {
        return $this->name;
    }

    public function description(string $description): static {
        $this->description = $description;
        return $this;
    }

    public function getDescription(): string {
        return $this->description;
    }

    abstract public function isValid(): bool;

    abstract public function jsonSerialize(): mixed;

    abstract public function load(mixed $value): void;

    abstract public function __toString(): string;
}