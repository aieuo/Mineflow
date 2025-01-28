<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable;

use aieuo\mineflow\exception\UnsupportedCalculationException;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\Tag;
use function array_reverse;
use function array_slice;
use function array_values;
use function iterator_to_array;
use function shuffle;

class ListVariable extends Variable implements IteratorVariable, \JsonSerializable {
    use IteratorVariableTrait;


    public static function getTypeName(): string {
        return "list";
    }

    /**
     * @param Variable[] $values
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

    public function shuffle(): ListVariable {
        $values = iterator_to_array($this->getIterator());
        shuffle($values);
        return new ListVariable(array_values($values));
    }

    public function take(int $amount): IteratorVariable {
        $values = iterator_to_array($this->getIterator());
        return new ListVariable(array_values(array_slice($values, 0, $amount)));
    }

    public function takeLast(int $amount): IteratorVariable {
        $values = iterator_to_array($this->getIterator());
        return new ListVariable(array_values(array_slice($values, -$amount, $amount)));
    }

    public function appendValue(Variable $value): void {
        $this->values[] = $value;
    }

    public function hasKey(int|string $key): bool {
        return isset($this->values[$key]);
    }

    public function setValueAt(int|string $key, Variable $value): void {
        $this->values[(int)$key] = $value;
        $this->values = array_values($this->values);
    }

    public function removeValueAt(int|string $index): void {
        unset($this->values[(int)$index]);
        $this->values = array_values($this->values);
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