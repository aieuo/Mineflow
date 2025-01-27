<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\argument;

use aieuo\mineflow\flowItem\argument\attribute\CustomFormEditorArgument;
use aieuo\mineflow\formAPI\element\Dropdown;
use function array_map;
use function array_search;
use function in_array;
use function is_int;

class StringEnumArgument extends FlowItemArgument implements CustomFormEditorArgument {

    public static function create(string $name, string $value = "", string $description = ""): static {
        return new static(name: $name, value: $value, description: $description);
    }

    /** @var string[] */
    private array $keys;

    /**
     * @param string $name
     * @param string $value
     * @param string[] $options
     * @param string $description
     * @param \Closure|null $keyFormatter
     */
    public function __construct(
        string                $name,
        private string        $value = "",
        private array         $options = [],
        string                $description = "",
        private \Closure|null $keyFormatter = null,
    ) {
        parent::__construct($name, $description);

        $this->keys = ($keyFormatter === null ? $options : array_map(fn(string $v) => $keyFormatter($v), $options));
    }

    public function value(string $value): self {
        $this->value = $value;
        return $this;
    }

    /**
     * @param string[] $options
     * @return $this
     */
    public function options(array $options): self {
        $this->options = $options;
        $this->keys = ($this->keyFormatter === null ? $options : array_map(fn(string $v) => ($this->keyFormatter)($v), $options));
        return $this;
    }

    public function getEnumValue(): string {
        return $this->value;
    }

    public function getEnumKey(): string {
        return $this->keyFormatter === null ? $this->getEnumValue() : ($this->keyFormatter)($this->getEnumValue());
    }

    /**
     * @param callable(string $value): string $formatter
     * @return $this
     */
    public function format(callable $formatter): self {
        $this->keyFormatter = $formatter;
        return $this;
    }

    public function isValid(): bool {
        return in_array($this->getEnumValue(), $this->options, true);
    }

    public function createFormElements(array $variables): array {
        $default = $this->getEnumKey();
        $options = $this->keys;
        $index = array_search($default, $options, true);
        return [
            new Dropdown($this->getDescription(), $options, $index === false ? 0 : $index)
        ];
    }

    /**
     * @param array{0: int} $data
     * @return void
     */
    public function handleFormResponse(mixed ...$data): void {
        $this->value($this->options[$data[0]]);
    }

    public function jsonSerialize(): string {
        return $this->getEnumValue();
    }

    public function load(mixed $value): void {
        if (is_int($value)) {
            $value = $this->options[$value] ?? $this->options[0] ?? "";
        }
        $this->value($value);
    }

    public function __toString(): string {
        return $this->getEnumKey();
    }
}