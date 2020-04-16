<?php

namespace aieuo\mineflow\variable;

class MapVariable extends ListVariable {

    public $type = Variable::MAP;

    public function addValue(Variable $value) {
        $this->value[$value->getName()] = $value;
    }

    public function __toString() {
        if (!empty($this->getShowString())) return $this->getShowString();
        $values = [];
        foreach ($this->getValue() as $key => $value) {
            $values[$key] = $value->__toString();
        }
        return str_replace(["{", "}", "\""] ,["<", ">", ""], json_encode($values));
    }

    public static function fromArray(array $data): ?Variable {
        if (!isset($data["value"])) return null;
        $values = [];
        foreach ($data["value"] as $name => $value) {
            if (!isset($value["type"])) return null;
            $values[$name] = Variable::create($value["value"], $value["name"] ?? "", $value["type"]);
        }
        return new self($values, $data["name"] ?? "");
    }
}