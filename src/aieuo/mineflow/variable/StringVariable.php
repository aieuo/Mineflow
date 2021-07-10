<?php

namespace aieuo\mineflow\variable;

use aieuo\mineflow\exception\UnsupportedCalculationException;

class StringVariable extends Variable implements \JsonSerializable {

    public $type = Variable::STRING;

    public function getValue(): string {
        return parent::getValue();
    }

    public function append(StringVariable $var): StringVariable {
        $result = $this->getValue().$var->getValue();
        return new StringVariable($result);
    }

    public function replace(StringVariable $var): StringVariable {
        $result = str_replace($var->getValue(), "", $this->getValue());
        return new StringVariable($result);
    }

    public function repeat(StringVariable $var): StringVariable {
        $result = str_repeat($this->getValue(), (int)$var->getValue());
        return new StringVariable($result);
    }

    public function split(StringVariable $var): ListVariable {
        $result = array_map(function (string $text) {
            return new StringVariable(trim($text));
        }, explode($var->getValue(), $this->getValue()));
        return new ListVariable($result);
    }

    public function add($target): Variable {
        return new StringVariable($this->getValue().$target);
    }

    public function sub($target): Variable {
        return new StringVariable(str_replace((string)$target, "", $this->getValue()));
    }

    public function mul($target): Variable {
        if ($target instanceof NumberVariable) $target = $target->getValue();
        if(is_numeric($target)) return new StringVariable(str_repeat($this->getValue(), (int)$target));

        throw new UnsupportedCalculationException();
    }

    public function callMethod(string $name, array $parameters = []): ?Variable {
        switch ($name) {
            case "length":
                return new NumberVariable(mb_strlen($this->getValue()));
            case "toLowerCase":
                return new StringVariable(mb_strtolower($this->getValue()));
            case "toUpperCase":
                return new StringVariable(mb_strtoupper($this->getValue()));
            case "substring":
                return new StringVariable(mb_substr($this->getValue(), $parameters[0], $parameters[1] ?? null));
        }
        return null;
    }

    public function jsonSerialize(): array {
        return [
            "type" => $this->getType(),
            "value" => $this->getValue(),
        ];
    }
}