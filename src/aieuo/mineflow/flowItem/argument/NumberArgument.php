<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\argument;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\Element;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\utils\Utils;

class NumberArgument extends FlowItemArgument {

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
        bool                  $optional = false,
    ) {
        parent::__construct($name, (string)$value, $description, $optional);
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

    /**
     * @throws \InvalidArgumentException
     */
    public function getInt(FlowItemExecutor $executor): int {
        return Utils::getInt($executor->replaceVariables($this->get()), $this->getMin(), $this->getMax(), $this->getExcludes());
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function getFloat(FlowItemExecutor $executor): float {
        return Utils::getFloat($executor->replaceVariables($this->get()), $this->getMin(), $this->getMax(), $this->getExcludes());
    }

    public function createFormElement(array $variables): Element {
        return new ExampleNumberInput(
            $this->getDescription(),
            $this->getExample(),
            $this->get(),
            required: !$this->isOptional(),
            min: (float)$this->getMin(),
            max: (float)$this->getMax(),
            excludes: $this->getExcludes(),
        );
    }
}
