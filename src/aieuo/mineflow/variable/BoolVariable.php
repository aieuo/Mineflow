<?php

namespace aieuo\mineflow\variable;

use aieuo\mineflow\exception\UnsupportedCalculationException;

class BoolVariable extends Variable implements \JsonSerializable {

    public int $type = Variable::BOOLEAN;

    public function __construct(bool $value) {
        parent::__construct($value);
    }

    public function getValue(): bool {
        return (bool)parent::getValue();
    }

    public function add($target): BoolVariable {
        if (!($target instanceof BoolVariable)) throw new UnsupportedCalculationException();

        return new BoolVariable($this->getValue() or $target->getValue());
    }

    public function mul($target): BoolVariable {
        if (!($target instanceof BoolVariable)) throw new UnsupportedCalculationException();

        return new BoolVariable($this->getValue() and $target->getValue());
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