<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\placeholder;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\Element;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\utils\Utils;

class NumberPlaceholder extends Placeholder {

    /**
     * @param string $name
     * @param string|float|int $value
     * @param string $description
     * @param string $example
     * @param float|null $min
     * @param float|null $max
     * @param float[] $excludes
     */
    public function __construct(
        string           $name,
        string|float|int $value = "",
        string           $description = "",
        private string   $example = "",
        private ?float   $min = null,
        private ?float   $max = null,
        private array    $excludes = [],
    ) {
        parent::__construct($name, (string)$value, $description);
    }

    public function setExample(string $example): void {
        $this->example = $example;
    }

    public function getExample(): string {
        return $this->example;
    }

    public function setMin(?float $min): void {
        $this->min = $min;
    }

    public function getMin(): ?float {
        return $this->min;
    }

    public function setMax(?float $max): void {
        $this->max = $max;
    }

    public function getMax(): ?float {
        return $this->max;
    }

    /**
     * @param float[] $excludes
     * @return void
     */
    public function setExcludes(array $excludes): void {
        $this->excludes = $excludes;
    }

    public function getExcludes(): array {
        return $this->excludes;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function getInt(FlowItemExecutor $executor): int {
        $number = $executor->replaceVariables($this->get());
        Utils::validateNumberString($number, $this->getMin(), $this->getMax(), $this->getExcludes());
        return (int)$number;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function getFloat(FlowItemExecutor $executor): float {
        $number = $executor->replaceVariables($this->get());
        Utils::validateNumberString($number, $this->getMin(), $this->getMax(), $this->getExcludes());
        return (float)$number;
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