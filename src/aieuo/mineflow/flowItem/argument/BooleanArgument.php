<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\argument;

use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\Element;
use aieuo\mineflow\formAPI\element\Toggle;

class BooleanArgument extends FlowItemArgument {

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

    public function inverse(): self {
        $this->inverseToggle = true;
        return $this;
    }

    public function isValid(): bool {
        return true;
    }

    public function createFormElement(array $variables): Element {
        return new Toggle($this->getDescription(), $this->inverseToggle ? !$this->getBool() : $this->getBool());
    }

    public function buildEditPage(SimpleEditFormBuilder $builder, array $variables): void {
        $processor = null;
        if ($this->inverseToggle) {
            $processor = fn(bool $value) => !$value;
        }
        $builder->element($this->createFormElement($variables), $processor);
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
