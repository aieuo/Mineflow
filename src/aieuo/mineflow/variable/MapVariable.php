<?php

namespace aieuo\mineflow\variable;

use aieuo\mineflow\utils\Language;
use Error;

class MapVariable extends Variable {

    public $type = Variable::MAP;

    /** @var string */
    private $showString = "";

    public function __construct($name, $value, ?string $str = "") {
        $this->name = $name;
        $this->value = $value;
        $this->showString = $str;
    }

    public function addition(Variable $var, string $name = "result"): Variable {
        if ($var->getType() !== Variable::LIST and $var->getType() !== Variable::MAP) {
            return new StringVariable("ERROR", Language::get("variable.map.add.error"));
        }
        $result = $this->getValue() + $var->getValue();
        return new ListVariable($name, $result);
    }

    public function subtraction(Variable $var, string $name = "result"): Variable {
        if ($var->getType() !== Variable::LIST) {
            return new StringVariable("ERROR", Language::get("variable.map.sub.error"));
        }
        $result = array_diff($this->getValue(), $var->getValue());
        return new ListVariable($name, $result);
    }

    public function multiplication(Variable $var, string $name = "result"): Variable {
        return new StringVariable("ERROR", Language::get("variable.map.mul.error"));
    }

    public function division(Variable $var, string $name = "result"): Variable {
        return new StringVariable("ERROR", Language::get("variable.map.div.error"));
    }

    public function modulo(Variable $var, string $name = "result"): Variable {
        return new StringVariable("ERROR", Language::get("variable.map.mod.error"));
    }

    public function getValueFromIndex($index) {
        if (!isset($this->value[$index])) return null;
        return $this->value[$index];
    }

    public function getCount() {
        return count($this->value);
    }

    public function toStringVariable(): StringVariable {
        $variable = new StringVariable($this->getName(), $this->__toString());
        return $variable;
    }

    public function __toString() {
        if (!empty($this->showString)) return $this->showString;
        $values = [];
        foreach ($this->getValue() as $key => $value) {
            if ($value instanceof Variable) {
                $value = $value->__toString();
            } elseif (is_array($value)) {
                if (array_values($value) === $value) $value = (new ListVariable("list", $value))->__toString();
                else $value = (new MapVariable("list", $value))->__toString();
            }
            $values[] = $key.": ".$value;
        }
        return "[".implode(", ", $values)."]";
    }
}