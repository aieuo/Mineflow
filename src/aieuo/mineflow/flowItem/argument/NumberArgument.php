<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\argument;

use aieuo\mineflow\flowItem\argument\attribute\CustomFormEditorArgument;
use aieuo\mineflow\flowItem\argument\attribute\Required;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\utils\Utils;
use aieuo\mineflow\variable\EvaluableString;

class NumberArgument extends FlowItemArgument implements CustomFormEditorArgument {
    use Required;

    public static function create(string $name, string|float|int|null $value = "", string $description = ""): static {
        return new static(name: $name, value: $value, description: $description);
    }

    private EvaluableString $value;

    /**
     * @param string $name
     * @param string|float|int|null $value
     * @param string $description
     * @param string $example
     * @param float|int|null $min
     * @param float|int|null $max
     * @param float[] $excludes
     * @param bool $optional
     */
    public function __construct(
        string                 $name,
        string|float|int|null  $value = "",
        string                 $description = "",
        private string         $example = "",
        private float|int|null $min = null,
        private float|int|null $max = null,
        private array          $excludes = [],
        bool                   $optional = false,
    ) {
        parent::__construct($name, $description);

        $this->value = new EvaluableString((string)$value);
        $optional ? $this->optional() : $this->required();
    }

    public function value(string|float|int $value): static {
        $this->value = new EvaluableString((string)$value);
        return $this;
    }

    public function getRawString(): string {
        return $this->value->getRaw();
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function getInt(FlowItemExecutor $executor): int {
        return Utils::getInt($this->value->eval($executor->getVariableRegistryCopy()), $this->getMin(), $this->getMax(), $this->getExcludes());
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function getFloat(FlowItemExecutor $executor): float {
        return Utils::getFloat($this->value->eval($executor->getVariableRegistryCopy()), $this->getMin(), $this->getMax(), $this->getExcludes());
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

    public function min(float|int|null $min): self {
        $this->min = $min;
        return $this;
    }

    public function getMin(): float|int|null {
        return $this->min;
    }

    public function max(float|int|null $max): self {
        $this->max = $max;
        return $this;
    }

    public function getMax(): float|int|null {
        return $this->max;
    }

    /**
     * @param float[] $excludes
     * @return NumberArgument
     */
    public function excludes(array $excludes): self {
        $this->excludes = $excludes;
        return $this;
    }

    public function getExcludes(): array {
        return $this->excludes;
    }

    public function isValid(): bool {
        return $this->isOptional() or $this->getRawString() !== "";
    }

    public function createFormElements(array $variables): array {
        return [new ExampleNumberInput(
            $this->getDescription(),
            $this->getExample(),
            $this->getRawString(),
            required: !$this->isOptional(),
            min: $this->getMin() === null ? null : (float)$this->getMin(),
            max: $this->getMax() === null ? null : (float)$this->getMax(),
            excludes: $this->getExcludes(),
        )];
    }

    /**
     * @param array{0: int|float} $data
     * @return void
     */
    public function handleFormResponse(mixed ...$data): void {
        $this->value($data[0]);
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

    public function __clone(): void {
        $this->value = clone $this->value;
    }
}