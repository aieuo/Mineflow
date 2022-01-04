<?php

namespace aieuo\mineflow\variable;

use aieuo\mineflow\exception\UnsupportedCalculationException;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\Main;

class MapVariable extends ListVariable {

    public int $type = Variable::MAP;

    public function getValueFromIndex(string $index): ?Variable {
        if (!isset($this->value[$index])) return null;
        return $this->value[$index];
    }

    public function setValueAt(int|string $key, Variable $value): void {
        $this->value[$key] = $value;
    }

    public function add($target): MapVariable {
        if ($target instanceof MapVariable) throw new UnsupportedCalculationException();

        $values = [];
        foreach ($this->getValue() as $key => $value) {
            $values[$key] = $value->add($target);
        }
        return new MapVariable($values);
    }

    public function sub($target): MapVariable {
        if ($target instanceof MapVariable) throw new UnsupportedCalculationException();

        $values = [];
        foreach ($this->getValue() as $key => $value) {
            $values[$key] = $value->sub($target);
        }
        return new MapVariable($values);
    }

    public function mul($target): MapVariable {
        if ($target instanceof MapVariable) throw new UnsupportedCalculationException();

        $values = [];
        foreach ($this->getValue() as $key => $value) {
            $values[$key] = $value->mul($target);
        }
        return new MapVariable($values);
    }

    public function div($target): MapVariable {
        if ($target instanceof MapVariable) throw new UnsupportedCalculationException();

        $values = [];
        foreach ($this->getValue() as $key => $value) {
            $values[$key] = $value->div($target);
        }
        return new MapVariable($values);
    }

    public function map(string|array|Variable $target, ?FlowItemExecutor $executor = null, array $variables = [], bool $global = false): MapVariable {
        $variableHelper = Main::getVariableHelper();
        $values = [];
        foreach ($this->getValue() as $key => $value) {
            $variables["it"] = $value;
            $values[$key] = $variableHelper->runAST($target, $executor, $variables, $global);
        }
        return new MapVariable($values);
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