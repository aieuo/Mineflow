<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\argument;

use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\Dropdown;
use aieuo\mineflow\formAPI\element\Element;
use function array_map;
use function array_search;
use function in_array;

class StringEnumArgument extends FlowItemArgument {

    /** @var string[] */
    private array $keys;

    /**
     * @param string $name
     * @param string $value
     * @param string[] $values
     * @param string $description
     * @param \Closure|null $keyFormatter
     */
    public function __construct(
        string                 $name,
        private string         $value = "",
        private readonly array $values = [],
        string                 $description = "",
        private \Closure|null  $keyFormatter = null,
    ) {
        parent::__construct($name, $description);

        $this->keys = ($keyFormatter === null ? $values : array_map(fn(string $v) => $keyFormatter($v), $values));
    }

    public function value(string $value): self {
        $this->value = $value;
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
        return in_array($this->getEnumValue(), $this->values, true);
    }

    public function createFormElement(array $variables): Element {
        $default = $this->getEnumKey();
        $options = $this->keys;
        $index = array_search($default, $options, true);
        return new Dropdown($this->getDescription(), $options, $index === false ? 0 : $index);
    }

    public function buildEditPage(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->element($this->createFormElement($variables), function (int $data) {
            return $this->values[$data];
        });
    }

    public function jsonSerialize(): string {
        return $this->getEnumValue();
    }

    public function load(mixed $value): void {
        $this->value($value);
    }

    public function __toString(): string {
        return $this->getEnumKey();
    }
}
