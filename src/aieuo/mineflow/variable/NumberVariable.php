<?php

namespace aieuo\mineflow\variable;

use aieuo\mineflow\exception\UnsupportedCalculationException;

class NumberVariable extends Variable implements \JsonSerializable {

    public $type = Variable::NUMBER;

    public static function zero(string $name = ""): self {
        return new NumberVariable(0, $name);
    }

    /**
     * @param int|float $value
     * @param string $name
     * @noinspection SenselessProxyMethodInspection
     */
    public function __construct($value, string $name = "") {
        parent::__construct($value, $name);
    }

    /**
     * @return int|float
     * @noinspection SenselessProxyMethodInspection
     */
    public function getValue() {
        return parent::getValue();
    }

    public function addition(NumberVariable $var, string $resultName = "result"): NumberVariable {
        $result = $this->getValue() + $var->getValue();
        return new NumberVariable($result, $resultName);
    }

    public function subtraction(NumberVariable $var, string $resultName = "result"): NumberVariable {
        $result = $this->getValue() - $var->getValue();
        return new NumberVariable($result, $resultName);
    }

    public function multiplication(NumberVariable $var, string $resultName = "result"): NumberVariable {
        $result = $this->getValue() * $var->getValue();
        return new NumberVariable($result, $resultName);
    }

    public function division(NumberVariable $var, string $resultName = "result"): NumberVariable {
        $result = $this->getValue() / $var->getValue();
        return new NumberVariable($result, $resultName);
    }

    public function modulo(NumberVariable $var, string $resultName = "result"): NumberVariable {
        $result = $this->getValue() % $var->getValue();
        return new NumberVariable($result, $resultName);
    }

    public function add($target): Variable {
        if ($target instanceof NumberVariable) return $this->addition($target);
        if (is_numeric($target)) return new NumberVariable($this->getValue() + $target, "result");

        throw new UnsupportedCalculationException();
    }

    public function sub($target): Variable {
        if ($target instanceof NumberVariable) return $this->subtraction($target);
        if (is_numeric($target)) return new NumberVariable($this->getValue() - $target, "result");

        throw new UnsupportedCalculationException();
    }

    public function mul($target): Variable {
        if ($target instanceof NumberVariable) return $this->multiplication($target);
        if (is_numeric($target)) return new NumberVariable($this->getValue() * $target, "result");

        throw new UnsupportedCalculationException();
    }

    public function div($target): Variable {
        if ($target instanceof NumberVariable) return $this->division($target);
        if (is_numeric($target)) return new NumberVariable($this->getValue() / $target, "result");

        throw new UnsupportedCalculationException();
    }

    public function toStringVariable(): StringVariable {
        return new StringVariable((string)$this->getValue(), $this->getName());
    }

    public function jsonSerialize(): array {
        return [
            "name" => $this->getName(), "type" => $this->getType(), "value" => $this->getValue(),
        ];
    }
}