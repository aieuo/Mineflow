<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\argument;

use aieuo\mineflow\flowItem\argument\attribute\CustomFormEditorArgument;
use aieuo\mineflow\flowItem\argument\attribute\Required;
use aieuo\mineflow\variable\EvaluableString;

abstract class ObjectVariableArgument extends FlowItemArgument implements CustomFormEditorArgument {
    use Required;

    private EvaluableString $value;

    public static function create(string $name, string $value = "", string $description = null): static {
        return new static(name: $name, value: $value, description: $description);
    }

    public function __construct(
        string $name,
        string $value = "",
        string $description = "",
        bool   $optional = false,
    ) {
        parent::__construct($name, $description);

        $this->value = new EvaluableString($value);
        $optional ? $this->optional() : $this->required();
    }

    public function value(string $value): self {
        $this->value = new EvaluableString($value);
        return $this;
    }

    public function getRawVariableName(): string {
        return $this->value->getRaw();
    }

    public function getVariableName(): EvaluableString {
        return $this->value;
    }

    public function isValid(): bool {
        return $this->isOptional() or $this->getVariableName()->getRaw() !== "";
    }

    public function jsonSerialize(): string {
        return $this->getVariableName()->getRaw();
    }

    /**
     * @param array{0: string} $data
     * @return void
     */
    public function handleFormResponse(mixed ...$data): void {
        $this->value($data[0]);
    }

    public function load(mixed $value): void {
        $this->value($value);
    }

    public function __toString(): string {
        return $this->getVariableName()->getRaw();
    }

    public function __clone(): void {
        $this->value = clone $this->value;
    }
}