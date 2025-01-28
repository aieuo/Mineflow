<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\argument;

use aieuo\mineflow\flowItem\argument\attribute\CustomFormEditorArgument;
use aieuo\mineflow\formAPI\element\Toggle;

class BooleanArgument extends FlowItemArgument implements CustomFormEditorArgument {

    public static function create(string $name, bool $value = false, string $description = ""): static {
        return new static(name: $name, value: $value, description: $description);
    }

    /**
     * @param string $name
     * @param bool $value
     * @param string $description
     * @param bool $inverseToggle
     * @param \Closure(bool $value): string|null $toStringFormatter
     */
    public function __construct(
        string                $name,
        private bool          $value = false,
        string                $description = "",
        private \Closure|null $toStringFormatter = null,
        private bool          $inverseToggle = false,
    ) {
        parent::__construct($name, $description);
    }

    public function value(bool $value): self {
        $this->value = $value;
        return $this;
    }

    public function getBool(): bool {
        return $this->value;
    }

    /**
     * @param callable(bool $value): string $formatter
     * @return $this
     */
    public function format(callable $formatter): self {
        $this->toStringFormatter = $formatter;
        return $this;
    }

    public function inverted(): self {
        $this->inverseToggle = true;
        return $this;
    }

    public function isValid(): bool {
        return true;
    }

    public function createFormElements(array $variables): array {
        return [
            new Toggle($this->getDescription(), $this->inverseToggle xor $this->getBool()),
        ];
    }

    /**
     * @param array{0: bool} $data
     * @return void
     */
    public function handleFormResponse(mixed ...$data): void {
        $this->value($data[0] xor $this->inverseToggle);
    }

    public function jsonSerialize(): bool {
        return $this->getBool();
    }

    public function load(mixed $value): void {
        $this->value($value);
    }

    public function __toString(): string {
        if ($this->toStringFormatter !== null) {
            return ($this->toStringFormatter)($this->getBool());
        }

        return $this->getBool() ? "true" : "false";
    }
}