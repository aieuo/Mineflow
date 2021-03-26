<?php

namespace aieuo\mineflow\variable;

class BoolVariable extends Variable implements \JsonSerializable {

    public $type = Variable::BOOLEAN;

    public function __construct(bool $value) {
        parent::__construct($value);
    }

    public function getValue(): bool {
        return (bool)parent::getValue();
    }

    public function __toString(): string {
        return $this->getValue() ? "true" : "false";
    }

    public function jsonSerialize(): array {
        return [
            "type" => $this->getType(),
            "value" => $this->getValue(),
        ];
    }
}