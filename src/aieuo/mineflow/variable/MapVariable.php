<?php

namespace aieuo\mineflow\variable;

class MapVariable extends ListVariable {

    public $type = Variable::MAP;

    public function getValueFromIndex(string $index): ?Variable {
        if (!isset($this->value[$index])) return null;
        return $this->value[$index];
    }

    /**
     * @param int|string $key
     * @param Variable $value
     */
    public function setValueAt($key, Variable $value): void {
        $this->value[$key] = $value;
    }

    public function __toString(): string {
        if (!empty($this->getShowString())) return $this->getShowString();
        $values = [];
        foreach ($this->getValue() as $key => $value) {
            $values[] = $key.":".$value;
        }
        return "<".implode(",", $values).">";
    }
}