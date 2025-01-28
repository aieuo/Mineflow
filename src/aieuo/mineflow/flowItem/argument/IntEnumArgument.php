<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\argument;

use aieuo\mineflow\flowItem\argument\attribute\CustomFormEditorArgument;
use aieuo\mineflow\formAPI\element\Dropdown;
use function count;

class IntEnumArgument extends FlowItemArgument implements CustomFormEditorArgument {

    public static function create(string $name, int $value = 0, string $description = ""): static {
        return new static(name: $name, value: $value, description: $description);
    }

    /**
     * @param string $name
     * @param int $value
     * @param string[] $options
     * @param string $description
     */
    public function __construct(
        string        $name,
        private int   $value = 0,
        private array $options = [],
        string        $description = "",
    ) {
        parent::__construct($name, $description);
    }

    public function value(int $enumValue): self {
        $this->value = $enumValue;
        return $this;
    }

    /**
     * @param string[] $options
     * @return $this
     */
    public function options(array $options): self {
        $this->options = $options;
        return $this;
    }

    public function getEnumValue(): int {
        return $this->value;
    }

    public function getEnumKey(): string {
        return $this->options[$this->getEnumValue()] ?? "";
    }

    public function isValid(): bool {
        return $this->getEnumValue() >= 0 and $this->getEnumValue() < count($this->options);
    }

    public function createFormElements(array $variables): array {
        return [
            new Dropdown($this->getDescription(), $this->options, $this->getEnumValue())
        ];
    }

    /**
     * @param array{0: int} $data
     * @return void
     */
    public function handleFormResponse(mixed ...$data): void {
        $this->value($data[0]);
    }

    public function jsonSerialize(): int {
        return $this->getEnumValue();
    }

    public function load(mixed $value): void {
        $this->value((int)$value);
    }

    public function __toString(): string {
        return $this->getEnumKey();
    }
}