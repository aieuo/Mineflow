<?php

namespace aieuo\mineflow\variable;

use aieuo\mineflow\exception\UnsupportedCalculationException;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\Mineflow;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\Tag;
use function array_reverse;
use function array_values;
use function count;

class MapVariable extends ListVariable {

    public int $type = Variable::MAP;

    public function getValueFromIndex(string $index): ?Variable {
        if (!isset($this->value[$index])) return null;
        return $this->value[$index];
    }

    public function setValueAt(int|string $key, Variable $value): void {
        $this->value[$key] = $value;
    }

    public function removeValueAt(int|string $index): void {
        unset($this->value[$index]);
    }

    public function removeValue(Variable $value, bool $strict = true): void {
        $index = $this->indexOf($value, $strict);
        if ($index === null) return;
        unset($this->value[$index]);
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
        $variableHelper = Mineflow::getVariableHelper();
        $values = [];
        foreach ($this->getValue() as $key => $value) {
            $variables["it"] = $value;
            $values[$key] = $variableHelper->runAST($target, $executor, $variables, $global);
        }
        return new MapVariable($values);
    }

    public function callMethod(string $name, array $parameters = []): ?Variable {
        $helper = Mineflow::getVariableHelper();
        return match ($name) {
            "count" => new NumberVariable(count($this->value)),
            "reverse" => new MapVariable(array_reverse($this->value)),
            "keys" => $helper->arrayToListVariable(array_keys($this->value)),
            "values" => new ListVariable(array_values($this->value)),
            default => null,
        };
    }

    public function toNBTTag(): Tag {
        $tag = CompoundTag::create();
        foreach ($this->getValue() as $key => $value) {
            $tag->setTag($key, $value->toNBTTag());
        }
        return $tag;
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
