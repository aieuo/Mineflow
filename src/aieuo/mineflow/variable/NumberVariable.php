<?php

namespace aieuo\mineflow\variable;

use aieuo\mineflow\exception\UnsupportedCalculationException;

class NumberVariable extends Variable implements \JsonSerializable {

    public $type = Variable::NUMBER;

    public static function zero(): self {
        return new NumberVariable(0);
    }

    /**
     * @param int|float $value
     * @noinspection SenselessProxyMethodInspection
     */
    public function __construct($value) {
        parent::__construct($value);
    }

    /**
     * @return int|float
     * @noinspection SenselessProxyMethodInspection
     */
    public function getValue() {
        return parent::getValue();
    }

    public function addition(NumberVariable $var): NumberVariable {
        $result = $this->getValue() + $var->getValue();
        return new NumberVariable($result);
    }

    public function subtraction(NumberVariable $var): NumberVariable {
        $result = $this->getValue() - $var->getValue();
        return new NumberVariable($result);
    }

    public function multiplication(NumberVariable $var): NumberVariable {
        $result = $this->getValue() * $var->getValue();
        return new NumberVariable($result);
    }

    public function division(NumberVariable $var): NumberVariable {
        $result = $this->getValue() / $var->getValue();
        return new NumberVariable($result);
    }

    public function modulo(NumberVariable $var): NumberVariable {
        $result = $this->getValue() % $var->getValue();
        return new NumberVariable($result);
    }

    public function add($target): Variable {
        if ($target instanceof NumberVariable) return $this->addition($target);
        if (is_numeric($target)) return new NumberVariable($this->getValue() + $target);

        throw new UnsupportedCalculationException();
    }

    public function sub($target): Variable {
        if ($target instanceof NumberVariable) return $this->subtraction($target);
        if (is_numeric($target)) return new NumberVariable($this->getValue() - $target);

        throw new UnsupportedCalculationException();
    }

    public function mul($target): Variable {
        if ($target instanceof NumberVariable) return $this->multiplication($target);
        if (is_numeric($target)) return new NumberVariable($this->getValue() * $target);

        throw new UnsupportedCalculationException();
    }

    public function div($target): Variable {
        if ($target instanceof NumberVariable) return $this->division($target);
        if (is_numeric($target)) return new NumberVariable($this->getValue() / $target);

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