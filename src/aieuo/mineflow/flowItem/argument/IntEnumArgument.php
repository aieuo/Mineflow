<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\argument;

use aieuo\mineflow\formAPI\element\Dropdown;
use aieuo\mineflow\formAPI\element\Element;
use function count;

class IntEnumArgument extends FlowItemArgument {

    /**
     * @param string $name
     * @param int $value
     * @param string[] $keys
     * @param string $description
     */
    public function __construct(
        string                 $name,
        int                    $value = 0,
        private readonly array $keys = [],
        string                 $description = "",
    ) {
        parent::__construct($name, $value, $description, false);
    }

    public function getValue(): int {
        return (int)$this->get();
    }

    public function getKey(): string {
        return $this->keys[$this->getValue()] ?? "";
    }

    public function isValid(): bool {
        return $this->getValue() >= 0 and $this->getValue() < count($this->keys);
    }

    public function createFormElement(array $variables): Element {
        return new Dropdown($this->getDescription(), $this->keys, $this->getValue());
    }

    public function __toString(): string {
        return $this->getKey();
    }
}
