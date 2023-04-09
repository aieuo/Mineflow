<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable;

use aieuo\mineflow\exception\UnsupportedCalculationException;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\Tag;
use function array_reverse;

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

    public static function registerProperties(string $class = self::class): void {
        self::registerMethod($class, "reverse", new VariableMethod(
            new DummyVariable(ListVariable::class),
            fn(array $values) => new ListVariable(array_reverse($values)),
        ), aliases: ["reversed"]);

        self::registerIteratorMethods($class);
    }
}
