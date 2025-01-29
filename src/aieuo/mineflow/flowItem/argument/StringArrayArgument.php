<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\argument;

use aieuo\mineflow\flowItem\argument\attribute\CustomFormEditorArgument;
use aieuo\mineflow\flowItem\argument\attribute\Required;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\variable\EvaluableString;
use function array_map;
use function explode;
use function implode;
use function is_string;

class StringArrayArgument extends FlowItemArgument implements CustomFormEditorArgument {
    use Required;

    public static function create(string $name, string|array $value = "", string $description = ""): static {
        return new static(name: $name, value: $value, description: $description);
    }

    /** @var EvaluableString[] */
    protected array $value;

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
        bool           $optional = false,
        private string $separator = ",",
    ) {
        parent::__construct($name, $description);

        if (is_string($value)) $value = array_map(trim(...), explode($this->separator, $value));
        $this->value = array_map(fn(string $value) => new EvaluableString($value), $value);
        $optional ? $this->optional() : $this->required();
    }

    public function value(array $value): self {
        $this->value = array_map(fn(string $value) => new EvaluableString($value), $value);
        return $this;
    }

    /**
     * @return string[]
     */
    public function getRawArray(): array {
        return array_map(fn(EvaluableString $value) => $value->getRaw(), $this->value);
    }

    /**
     * @param FlowItemExecutor $executor
     * @return string[]
     */
    public function getArray(FlowItemExecutor $executor): array {
        return array_map(fn(EvaluableString $value) => $value->eval($executor->getVariableRegistryCopy()), $this->value);
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

    public function createFormElements(array $variables): array {
        return [
            new ExampleInput($this->getDescription(), $this->getExample(), $this->getRawString(), required: !$this->isOptional())
        ];
    }

    /**
     * @param array{0: string} $data
     * @return void
     */
    public function handleFormResponse(mixed ...$data): void {
        $values = array_map(trim(...), explode($this->separator, $data[0]));
        $this->value($values);
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

    public function __clone(): void {
        $values = [];
        foreach ($this->value as $value) {
            $values[] = clone $value;
        }
        $this->value = $values;
    }
}