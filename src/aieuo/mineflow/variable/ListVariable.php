<?php

namespace aieuo\mineflow\variable;

use aieuo\mineflow\utils\Language;

class ListVariable extends Variable {

    public $type = Variable::LIST;

    /** @var string */
    private $showString = "";

    public function __construct($name, $value, ?string $str = "") {
        $this->name = $name;
        $this->value = $value;
        $this->showString = $str;
    }

    public function addition(Variable $var, string $name = "result"): Variable {
        if ($var->getType() !== Variable::LIST) {
            return new StringVariable("ERROR", Language::get("variable.list.add.error"));
        }
        $result = array_merge($this->getValue(), $var->getValue());
        return new ListVariable($name, $result);
    }

    public function subtraction(Variable $var, string $name = "result"): Variable {
        if ($var->getType() !== Variable::LIST) {
            return new StringVariable("ERROR", Language::get("variable.list.sub.error"));
        }
        $result = array_diff($this->getValue(), $var->getValue());
        $result = array_values($result);
        return new ListVariable($name, $result);
    }

    public function multiplication(Variable $var, string $name = "result"): Variable {
        if ($var->getType() !== Variable::NUMBER) {
            return new StringVariable("ERROR", Language::get("variable.list.mul.error"));
        }
        $result = [];
        $max = (int)$var->getValue();
        for ($i=0; $i<$max; $i ++) {
            $result = array_merge($result, $this->getValue());
        }
        return new ListVariable($name, $result);
    }

    public function division(Variable $var, string $name = "result"): Variable {
        return new StringVariable("ERROR", Language::get("variable.list.div.error"));
    }

    public function modulo(Variable $var, string $name = "result"): Variable {
        return new StringVariable("ERROR", Language::get("variable.list.mod.error"));
    }

    public function addValue($value) {
        $this->value[] = $value;
    }

    public function removeValue($value) {
        $index = array_search($value, $this->value);
        if ($index === false) return;
        unset($this->value[$index]);
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
        foreach ($this->getValue() as $value) {
            if ($value instanceof Variable) {
                $value = $value->__toString();
            } elseif (is_array($value)) {
                if (array_values($value) === $value) $value = (new ListVariable("list", $value))->__toString();
                else $value = (new MapVariable("list", $value))->__toString();
            }
            $values[] = $value;
        }
        return "[".implode(", ", $values)."]";
    }
}