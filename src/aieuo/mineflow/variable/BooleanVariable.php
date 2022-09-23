<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable;

use aieuo\mineflow\exception\UnsupportedCalculationException;

class BooleanVariable extends Variable implements \JsonSerializable {

    public static function getTypeName(): string {
        return "boolean";
    }

    public function __construct(bool $value) {
        parent::__construct($value);
    }

    public function getValue(): bool {
        return (bool)parent::getValue();
    }

    public function add(Variable $target): BooleanVariable {
        if (!($target instanceof BooleanVariable)) throw new UnsupportedCalculationException();

        return new BooleanVariable($this->getValue() or $target->getValue());
    }

    public function mul(Variable $target): BooleanVariable {
        if (!($target instanceof BooleanVariable)) throw new UnsupportedCalculationException();

        return new BooleanVariable($this->getValue() and $target->getValue());
    }

    public function __toString(): string {
        return $this->getValue() ? "true" : "false";
    }

    public function jsonSerialize(): array {
        return [
            "type" => static::getTypeName(),
            "value" => $this->getValue(),
        ];
    }
}