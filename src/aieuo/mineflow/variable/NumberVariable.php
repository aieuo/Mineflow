<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable;

use aieuo\mineflow\exception\UnsupportedCalculationException;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\Tag;
use function abs;
use function ceil;
use function floor;
use function is_int;
use function round;
use function str_pad;
use const STR_PAD_LEFT;

class NumberVariable extends Variable implements \JsonSerializable {

    public static function zero(): self {
        return new NumberVariable(0);
    }

    public static function getTypeName(): string {
        return "number";
    }

    public function __construct(private int|float $value) {
    }

    public function getValue(): int|float {
        return $this->value;
    }

    public function add(Variable $target): NumberVariable {
        if ($target instanceof NumberVariable) return new NumberVariable($this->getValue() + $target->getValue());

        throw new UnsupportedCalculationException();
    }

    public function sub(Variable $target): NumberVariable {
        if ($target instanceof NumberVariable) return new NumberVariable($this->getValue() - $target->getValue());

        throw new UnsupportedCalculationException();
    }

    public function mul(Variable $target): NumberVariable {
        if ($target instanceof NumberVariable) return new NumberVariable($this->getValue() * $target->getValue());

        throw new UnsupportedCalculationException();
    }

    public function div(Variable $target): NumberVariable {
        if ($target instanceof NumberVariable) return new NumberVariable($this->getValue() / $target->getValue());

        throw new UnsupportedCalculationException();
    }

    public function toNBTTag(): Tag {
        if (is_int($this->value)) {
            return new IntTag($this->value);
        }

        return new FloatTag((float)$this->value);
    }

    public function jsonSerialize(): array {
        return [
            "type" => static::getTypeName(),
            "value" => $this->getValue(),
        ];
    }

    public static function registerProperties(string $class = self::class): void {
        self::registerProperty($class, "ceil", new VariableProperty(
            new DummyVariable(NumberVariable::class),
            fn(int|float $value) => new NumberVariable(ceil($value)),
        ));
        self::registerMethod($class, "ceil", new VariableMethod(
            new DummyVariable(NumberVariable::class),
            fn(int|float $value) => new NumberVariable(ceil($value)),
        ));
        self::registerProperty($class, "floor", new VariableProperty(
            new DummyVariable(NumberVariable::class),
            fn(int|float $value) => new NumberVariable(floor($value)),
        ));
        self::registerMethod($class, "floor", new VariableMethod(
            new DummyVariable(NumberVariable::class),
            fn(int|float $value) =>  new NumberVariable(floor($value)),
        ));
        self::registerProperty($class, "round", new VariableProperty(
            new DummyVariable(NumberVariable::class),
            fn(int|float $value) => new NumberVariable(round($value)),
        ));
        self::registerMethod($class, "round", new VariableMethod(
            new DummyVariable(NumberVariable::class),
            fn(int|float $value, $precision) =>  new NumberVariable(round($value, (int)$precision)),
        ));
        self::registerProperty($class, "abs", new VariableProperty(
            new DummyVariable(NumberVariable::class),
            fn(int|float $value) => new NumberVariable(abs($value)),
        ));
        self::registerMethod($class, "abs", new VariableMethod(
            new DummyVariable(NumberVariable::class),
            fn(int|float $value) =>  new NumberVariable(abs($value)),
        ));
        self::registerMethod($class, "pad", new VariableMethod(
            new DummyVariable(NumberVariable::class),
            fn(int|float $value, $length) =>  new StringVariable(str_pad((string)$value, (int)$length, "0", STR_PAD_LEFT)),
        ));
    }
}