<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\argument;

abstract class ObjectVariableArgument extends FlowItemArgument {
    public function __construct(
        string         $name,
        private string $value = "",
        string         $description = "",
        private bool   $optional = false,
    ) {
        parent::__construct($name, $description);
    }

    public function value(string $value): self {
        $this->value = $value;
        return $this;
    }

    public function getVariableName(): ?string {
        return $this->value;
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
