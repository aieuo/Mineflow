<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable;

use aieuo\mineflow\exception\UnsupportedCalculationException;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\Tag;
use function array_reverse;

class MapVariable extends Variable implements IteratorVariable {
    use IteratorVariableTrait;

    public static function getTypeName(): string {
        return "map";
    }

    /**
     * @param array<string, Variable> $values
     * @param string|null $showString
     */
    public function __construct(protected array $values, private ?string $showString = "") {
    }

    public function getValue(): array {
        return $this->values;
    }

    public function getIterator(): \Traversable {
        foreach ($this->values as $key => $value) {
            yield $key => $value;
        }
    }

    public function hasKey(int|string $key): bool {
        return isset($this->values[(int)$key]);
    }

    public function setValueAt(int|string $key, Variable $value): void {
        $this->values[$key] = $value;
    }

    public function removeValueAt(int|string $index): void {
        unset($this->values[$index]);
    }

    protected function getValueFromIndex(string $index): ?Variable {
        return $this->values[$index] ?? $this->pluck($index);
    }

    public function add(Variable $target): MapVariable {
        if ($target instanceof IteratorVariable) throw new UnsupportedCalculationException();

        $values = [];
        foreach ($this->getIterator() as $key => $value) {
            $values[$key] = $value->add($target);
        }
        return new MapVariable($values);
    }

    public function sub(Variable $target): MapVariable {
        if ($target instanceof IteratorVariable) throw new UnsupportedCalculationException();

        $values = [];
        foreach ($this->getIterator() as $key => $value) {
            $values[$key] = $value->sub($target);
        }
        return new MapVariable($values);
    }

    public function mul(Variable $target): MapVariable {
        if ($target instanceof IteratorVariable) throw new UnsupportedCalculationException();

        $values = [];
        foreach ($this->getIterator() as $key => $value) {
            $values[$key] = $value->mul($target);
        }
        return new MapVariable($values);
    }

    public function div(Variable $target): MapVariable {
        if ($target instanceof IteratorVariable) throw new UnsupportedCalculationException();

        $values = [];
        foreach ($this->getIterator() as $key => $value) {
            $values[$key] = $value->div($target);
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

    public function getShowString(): string {
        return $this->showString;
    }

    public function toNBTTag(): Tag {
        $tag = CompoundTag::create();
        foreach ($this->getValue() as $key => $value) {
            $tag->setTag($key, $value->toNBTTag());
        }
        return $tag;
    }

    public static function registerProperties(string $class = self::class): void {
        self::registerMethod($class, "reverse", new VariableMethod(
            new DummyVariable(MapVariable::class),
            fn(array $values) => new MapVariable(array_reverse($values)),
        ), aliases: ["reversed"]);

        self::registerIteratorMethods($class);
    }
}