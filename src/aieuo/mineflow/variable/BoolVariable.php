<?php

namespace aieuo\mineflow\variable;

class BoolVariable extends Variable implements \JsonSerializable {

    public $type = Variable::BOOLEAN;

    public function __construct(bool $value, string $name = "") {
        parent::__construct($value, $name);
    }

    public function getValue(): bool {
        return (bool)parent::getValue();
    }

    public function __toString(): string {
        return $this->getValue() ? "true" : "false";
    }

    public function jsonSerialize(): array {
        return [
            "name" => $this->getName(), "type" => $this->getType(), "value" => $this->getValue(),
        ];
    }
}