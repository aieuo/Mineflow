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

    public function __construct(int|float $value) {
        parent::__construct($value);
    }

    public function getValue(): int|float {
        return parent::getValue();
    }

    public function modulo(NumberVariable $var): NumberVariable {
        $result = $this->getValue() % $var->getValue();
        return new NumberVariable($result);
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

    public function toStringVariable(): StringVariable {
        return new StringVariable((string)$this->getValue());
    }

    public function jsonSerialize(): array {
        return [
            "type" => static::getTypeName(),
            "value" => $this->getValue(),
        ];
    }
}
