<?php

namespace aieuo\mineflow\variable;

use aieuo\mineflow\exception\UnsupportedCalculationException;

class StringVariable extends Variable implements \JsonSerializable {

    public $type = Variable::STRING;

    public function getValue(): string {
        return parent::getValue();
    }

    public function append(StringVariable $var, string $resultName = "result"): StringVariable {
        $result = $this->getValue().$var->getValue();
        return new StringVariable($result, $resultName);
    }

    public function replace(StringVariable $var, string $resultName = "result"): StringVariable {
        $result = str_replace($var->getValue(), "", $this->getValue());
        return new StringVariable($result, $resultName);
    }

    public function repeat(StringVariable $var, string $resultName = "result"): StringVariable {
        $result = str_repeat($this->getValue(), (int)$var->getValue());
        return new StringVariable($result, $resultName);
    }

    public function split(StringVariable $var, string $resultName = "result"): ListVariable {
        $result = array_map(function (string $text) {
            return new StringVariable(trim($text));
        }, explode($var->getValue(), $this->getValue()));
        return new ListVariable($result, $resultName);
    }

    public function add($target): Variable {
        return new StringVariable($this->getValue().$target, "result");
    }

    public function sub($target): Variable {
        return new StringVariable(str_replace((string)$target, "", $this->getValue()), "result");
    }

    public function mul($target): Variable {
        if ($target instanceof NumberVariable) $target = $target->getValue();
        if(is_numeric($target)) new StringVariable(str_repeat($this->getValue(), (int)$target), "result");

        throw new UnsupportedCalculationException();
    }

    public function jsonSerialize(): array {
        return [
            "name" => $this->getName(),
            "type" => $this->getType(),
            "value" => $this->getValue(),
        ];
    }
}