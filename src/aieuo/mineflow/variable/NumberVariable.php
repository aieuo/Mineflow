<?php

namespace aieuo\mineflow\variable;

use aieuo\mineflow\exception\UnsupportedCalculationException;

class NumberVariable extends Variable implements \JsonSerializable {

    public int $type = Variable::NUMBER;

    public static function zero(): self {
        return new NumberVariable(0);
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
            "type" => $this->getType(),
            "value" => $this->getValue(),
        ];
    }
}