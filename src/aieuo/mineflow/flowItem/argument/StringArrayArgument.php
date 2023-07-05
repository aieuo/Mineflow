<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\argument;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\Element;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use function array_map;
use function explode;
use function implode;
use function is_string;

class StringArrayArgument extends FlowItemArgument {

    /** @var string[] */
    private array $value;

    /**
     * @param string $name
     * @param string|string[] $value
     * @param string $description
     * @param string $example
     * @param bool $optional
     * @param string $separator
     */
    public function __construct(
        string         $name,
        string|array   $value = [],
        string         $description = "",
        private string $example = "",
        private bool   $optional = false,
        private string $separator = ",",
    ) {
        parent::__construct($name, $description);

        if (is_string($value)) $value = array_map(trim(...), explode($this->separator, $value));
        $this->value = $value;
    }

    public function value(array $value): self {
        $this->value = $value;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getRawArray(): array {
        return $this->value;
    }

    /**
     * @param FlowItemExecutor $executor
     * @return string[]
     */
    public function getArray(FlowItemExecutor $executor): array {
        return array_map(fn(string $value) => $executor->replaceVariables($value), $this->getRawArray());
    }

    public function getRawString(): string {
        return implode($this->separator, $this->getRawArray());
    }

    public function getString(FlowItemExecutor $executor): string {
        return implode($this->separator, $this->getArray($executor));
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

    public function separator(string $separator): self {
        $this->separator = $separator;
        return $this;
    }

    public function getSeparator(): string {
        return $this->separator;
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

    public function buildEditPage(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->element($this->createFormElement($variables), function (string $data) {
            return array_map(trim(...), explode($this->separator, $data));
        });
    }

    public function jsonSerialize(): array {
        return $this->getRawArray();
    }

    public function load(mixed $value): void {
        $this->value($value);
    }

    public function __toString(): string {
        return $this->getRawString();
    }
}
