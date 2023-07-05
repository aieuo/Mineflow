<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\argument;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\Element;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\utils\Utils;

class NumberArgument extends FlowItemArgument {

    private string $value;

    /**
     * @param string $name
     * @param string|float|int|null $value
     * @param string $description
     * @param string $example
     * @param float|null $min
     * @param float|null $max
     * @param float[] $excludes
     * @param bool $optional
     */
    public function __construct(
        string                $name,
        string|float|int|null $value = "",
        string                $description = "",
        private string        $example = "",
        private ?float        $min = null,
        private ?float        $max = null,
        private array         $excludes = [],
        private bool          $optional = false,
    ) {
        parent::__construct($name, $description);

        $this->value = (string)$value;
    }

    public function value(string|float|int $value): static {
        $this->value = (string)$value;
        return $this;
    }

    public function getRawString(): string {
        return $this->value;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function getInt(FlowItemExecutor $executor): int {
        return Utils::getInt($executor->replaceVariables($this->getRawString()), $this->getMin(), $this->getMax(), $this->getExcludes());
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function getFloat(FlowItemExecutor $executor): float {
        return Utils::getFloat($executor->replaceVariables($this->getRawString()), $this->getMin(), $this->getMax(), $this->getExcludes());
    }

    public function example(string $example): self {
        $this->example = $example;
        return $this;
    }

    public function setExample(string $example): void {
        $this->example = $example;
    }

    public function getExample(): string {
        return $this->example;
    }

    public function min(?float $min): void {
        $this->min = $min;
    }

    public function getMin(): ?float {
        return $this->min;
    }

    public function max(?float $max): void {
        $this->max = $max;
    }

    public function getMax(): ?float {
        return $this->max;
    }

    /**
     * @param float[] $excludes
     * @return void
     */
    public function excludes(array $excludes): void {
        $this->excludes = $excludes;
    }

    public function getExcludes(): array {
        return $this->excludes;
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
        return new ExampleNumberInput(
            $this->getDescription(),
            $this->getExample(),
            $this->getRawString(),
            required: !$this->isOptional(),
            min: (float)$this->getMin(),
            max: (float)$this->getMax(),
            excludes: $this->getExcludes(),
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
