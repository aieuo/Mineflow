<?php

namespace aieuo\mineflow\variable;

class NullVariable extends Variable implements \JsonSerializable {

    public int $type = Variable::NULL;

    public function __construct() {
        parent::__construct(null);
    }

    public function __toString(): string {
        return "null";
    }

    public function jsonSerialize(): array {
        return [
            "type" => $this->getType(),
            "value" => $this->getValue(),
        ];
    }
}