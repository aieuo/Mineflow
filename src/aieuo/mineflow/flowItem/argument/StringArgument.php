<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\argument;

use aieuo\mineflow\flowItem\argument\attribute\CustomFormEditorArgument;
use aieuo\mineflow\flowItem\argument\attribute\Required;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\variable\EvaluableString;

class StringArgument extends FlowItemArgument implements CustomFormEditorArgument {
    use Required;

    private EvaluableString $value;

    public static function create(string $name, string $value = "", string $description = ""): static {
        return new static(name: $name, value: $value, description: $description);
    }

    public function __construct(
        string         $name,
        string         $value = "",
        string         $description = "",
        private string $example = "",
        bool           $optional = false,
    ) {
        parent::__construct($name, $description);

        $this->value = new EvaluableString($value);
        $optional ? $this->optional() : $this->required();
    }

    public function value(string $value): self {
        $this->value = new EvaluableString($value);
        return $this;
    }

    public function getRawString(): string {
        return $this->value->getRaw();
    }

    public function getString(FlowItemExecutor $executor): string {
        return $this->value->eval($executor->getVariableRegistryCopy());
    }

    public function example(string $example): self {
        $this->example = $example;
        return $this;
    }

    public function getExample(): string {
        return $this->example;
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