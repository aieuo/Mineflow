<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable;

use aieuo\mineflow\exception\UnsupportedCalculationException;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\Tag;
use function array_reverse;
use function array_search;
use function array_values;

class ListVariable extends Variable implements IteratorVariable, \JsonSerializable {
    use IteratorVariableTrait;

    private ?string $showString;

    public static function getTypeName(): string {
        return "list";
    }

    /**
     * @param Variable[] $values
     * @param string|null $str
     */
    public function __construct(protected array $values, ?string $str = "") {
        $this->showString = $str;
    }

    public function getValue(): array {
        return $this->values;
    }

    public function getIterator(): \Traversable {
        foreach ($this->values as $key => $value) {
            yield $key => $value;
        }
    }

    public function appendValue(Variable $value): void {
        $this->values[] = $value;
    }

    public function setValueAt(int|string $key, Variable $value): void {
        $this->values[(int)$key] = $value;
        $this->values = array_values($this->values);
    }

    public function removeValue(Variable $value, bool $strict = true): void {
        $index = $this->indexOf($value, $strict);
        if ($index === false) return;
        unset($this->values[$index]);
        $this->values = array_values($this->values);
    }

    public function removeValueAt(int|string $index): void {
        unset($this->values[(int)$index]);
        $this->values = array_values($this->values);
    }

    public function indexOf(Variable $value, bool $strict = true): int|string|null {
        if ($strict) {
            $index = array_search($value, $this->values, true);
            return $index === false ? null : $index;
        }

        $str = (string)$value;
        foreach ($this->values as $index => $v) {
            if ((string)$v === $str) return $index;
        }
        return null;
    }

    protected function getValueFromIndex(string $index): ?Variable {
        return $this->values[$index] ?? $this->pluck($index);
    }

    public function add(Variable $target): ListVariable {
        if ($target instanceof ListVariable) throw new UnsupportedCalculationException();

        $values = [];
        foreach ($this->getValue() as $value) {
            $values[] = $value->add($target);
        }
        return new ListVariable($values);
    }

    public function sub(Variable $target): ListVariable {
        if ($target instanceof ListVariable) throw new UnsupportedCalculationException();

        $values = [];
        foreach ($this->getValue() as $value) {
            $values[] = $value->sub($target);
        }
        return new ListVariable($values);
    }

    public function mul(Variable $target): ListVariable {
        if ($target instanceof ListVariable) throw new UnsupportedCalculationException();

        $values = [];
        foreach ($this->getValue() as $value) {
            $values[] = $value->mul($target);
        }
        return new ListVariable($values);
    }

    public function div(Variable $target): ListVariable {
        if ($target instanceof ListVariable) throw new UnsupportedCalculationException();

        $values = [];
        foreach ($this->getValue() as $value) {
            $values[] = $value->div($target);
        }
        return new ListVariable($values);
    }

    public function __toString(): string {
        if (!empty($this->getShowString())) return $this->getShowString();

        $values = [];
        foreach ($this->getValue() as $value) {
            $values[] = (string)$value;
        }
        return "[".implode(",", $values)."]";
    }

    public function getShowString(): string {
        return $this->showString;
    }

    public function toArray(): array {
        $result = [];
        foreach ($this->getValue() as $i => $value) {
            if ($value instanceof ListVariable) $result[$i] = $value->toArray();
            else $result[$i] = (string)$value;
        }
        return $result;
    }

    public function toNBTTag(): Tag {
        $result = [];
        foreach ($this->getValue() as $value) {
            $result[] = $value->toNBTTag();
        }
        return new ListTag($result);
    }

    public function jsonSerialize(): array {
        return [
            "type" => static::getTypeName(),
            "value" => $this->getValue(),
        ];
    }

    public static function registerProperties(string $class = self::class): void {
        self::registerMethod($class, "reverse", new VariableMethod(
            new DummyVariable(ListVariable::class),
            fn(array $values) => new ListVariable(array_reverse($values)),
        ), aliases: ["reversed"]);

        self::registerIteratorMethods($class);
    }
}
