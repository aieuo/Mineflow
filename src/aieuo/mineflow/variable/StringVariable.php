<?php

namespace aieuo\mineflow\variable;

use aieuo\mineflow\utils\Language;

class StringVariable extends Variable {

    public $type = Variable::STRING;

    public function addition(Variable $var, string $name = "result"): Variable {
        $result = $this->getValue().$var->getValue();
        return new StringVariable($name, $result);
    }

    public function subtraction(Variable $var, string $name = "result"): Variable {
        $result = str_replace((string)$var->getValue(), "", $this->getValue());
        return new StringVariable($name, $result);
    }

    public function multiplication(Variable $var, string $name = "result"): Variable {
        if ($var->getType() !== Variable::NUMBER) {
            return new StringVariable("ERROR", Language::get("variable.string.mul.error"));
        }
        if ($var->getValue() <= 0) {
            return new StringVariable("ERROR", Language::get("variable.string.mul.0"));
        }
        $result = str_repeat($this->getValue(), (int)$var->getValue());
        return new StringVariable($name, $result);
    }

    public function division(Variable $var, string $name = "result"): Variable {
        if ($var->getType() !== Variable::STRING) {
            return new StringVariable("ERROR", Language::get("variable.string.div.error"));
        }
        $result = array_map(function ($value) {
            return trim(rtrim($value));
        }, explode((string)$var->getValue(), (string)$this->getValue()));
        return new ListVariable($name, $result);
    }

    public function modulo(Variable $var, string $name = "result"): Variable {
        return new StringVariable("ERROR", Language::get("variable.string.mod.error"));
    }

    public function toStringVariable(): StringVariable {
        return $this;
    }

    public function __toString() {
        return $this->getValue();
    }
}