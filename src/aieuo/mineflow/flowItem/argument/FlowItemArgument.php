<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\argument;

use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\Element;

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

    abstract public function createFormElement(array $variables): Element;

    public function buildEditPage(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->element($this->createFormElement($variables));
    }

    abstract public function jsonSerialize(): mixed;

    abstract public function load(mixed $value): void;

    abstract public function __toString(): string;
}
