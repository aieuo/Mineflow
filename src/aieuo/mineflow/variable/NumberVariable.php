<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable;

use aieuo\mineflow\exception\UnsupportedCalculationException;

class NumberVariable extends Variable implements \JsonSerializable {

    public static function zero(): self {
        return new NumberVariable(0);
    }

    public static function getTypeName(): string {
        return "number";
    }

    public function __construct(private int|float $value) {
    }

    public function getValue(): int|float {
        return $this->value;
    }

    public function add(Variable $target): NumberVariable {
        if ($target instanceof NumberVariable) return new NumberVariable($this->getValue() + $target->getValue());

        throw new UnsupportedCalculationException();
    }

    public function sub(Variable $target): NumberVariable {
        if ($target instanceof NumberVariable) return new NumberVariable($this->getValue() - $target->getValue());

        throw new UnsupportedCalculationException();
    }

    public function mul(Variable $target): NumberVariable {
        if ($target instanceof NumberVariable) return new NumberVariable($this->getValue() * $target->getValue());

        throw new UnsupportedCalculationException();
    }

    public function div(Variable $target): NumberVariable {
        if ($target instanceof NumberVariable) return new NumberVariable($this->getValue() / $target->getValue());

        throw new UnsupportedCalculationException();
    }

    public function jsonSerialize(): array {
        return [
            "type" => static::getTypeName(),
            "value" => $this->getValue(),
        ];
    }
}
