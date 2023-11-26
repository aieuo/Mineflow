<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\argument;

use aieuo\mineflow\flowItem\argument\attribute\Required;

abstract class ObjectVariableArgument extends FlowItemArgument {
    use Required;

    public static function create(string $name, string $value = "", string $description = ""): static {
        return new static(name: $name, value: $value, description: $description);
    }

    public function __construct(
        string         $name,
        private string $value = "",
        string         $description = "",
        bool           $optional = false,
    ) {
        parent::__construct($name, $description);

        $optional ? $this->optional() : $this->required();
    }

    public function value(string $value): self {
        $this->value = $value;
        return $this;
    }

    public function getVariableName(): ?string {
        return $this->value;
    }

    public function isValid(): bool {
        return $this->isOptional() or $this->getVariableName() !== "";
    }

    public function jsonSerialize(): string {
        return $this->getVariableName();
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
        return $this->getVariableName();
    }
}
