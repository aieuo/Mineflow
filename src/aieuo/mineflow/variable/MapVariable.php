<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable;

use aieuo\mineflow\exception\UnsupportedCalculationException;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\Main;
use function array_reverse;
use function array_values;
use function count;

class MapVariable extends ListVariable {

    public static function getTypeName(): string {
        return "map";
    }

    public function setValueAt(int|string $key, Variable $value): void {
        $this->values[$key] = $value;
    }

    public function removeValue(Variable $value, bool $strict = true): void {
        $index = $this->indexOf($value, $strict);
        if ($index === false) return;
        unset($this->values[$index]);
    }

    public function removeValueAt(int|string $index): void {
        unset($this->values[$index]);
    }

    public function getValueFromIndex(string $index): ?Variable {
        return $this->values[$index] ?? null;
    }

    public function add(Variable $target): MapVariable {
        if ($target instanceof MapVariable) throw new UnsupportedCalculationException();

        $values = [];
        foreach ($this->getValue() as $key => $value) {
            $values[$key] = $value->add($target);
        }
        return new MapVariable($values);
    }

    public function sub(Variable $target): MapVariable {
        if ($target instanceof MapVariable) throw new UnsupportedCalculationException();

        $values = [];
        foreach ($this->getValue() as $key => $value) {
            $values[$key] = $value->sub($target);
        }
        return new MapVariable($values);
    }

    public function mul(Variable $target): MapVariable {
        if ($target instanceof MapVariable) throw new UnsupportedCalculationException();

        $values = [];
        foreach ($this->getValue() as $key => $value) {
            $values[$key] = $value->mul($target);
        }
        return new MapVariable($values);
    }

    public function div(Variable $target): MapVariable {
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

    public function callMethod(string $name, array $parameters = []): ?Variable {
        $helper = Main::getVariableHelper();
        return match ($name) {
            "count" => new NumberVariable(count($this->value)),
            "reverse" => new MapVariable(array_reverse($this->value)),
            "keys" => $helper->arrayToListVariable(array_keys($this->value)),
            "values" => new ListVariable(array_values($this->value)),
            default => null,
        };
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
