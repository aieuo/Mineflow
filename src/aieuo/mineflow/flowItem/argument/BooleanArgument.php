<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\argument;

use aieuo\mineflow\formAPI\element\Element;
use aieuo\mineflow\formAPI\element\Toggle;

class BooleanArgument extends FlowItemArgument {

    public function __construct(
        string $name,
        bool   $value = false,
        string $description = "",
    ) {
        parent::__construct($name, $value, $description, false);
    }

    public function getBool(): bool {
        return $this->get();
    }

    public function createFormElement(array $variables): Element {
        return new Toggle($this->getDescription(), $this->getBool());
    }

    public function __toString(): string {
        return $this->getBool() ? "true" : "false";
    }
}
