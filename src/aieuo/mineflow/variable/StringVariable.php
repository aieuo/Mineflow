<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable;

use aieuo\mineflow\exception\UnsupportedCalculationException;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\Tag;
use function mb_strlen;
use function mb_strtolower;
use function mb_substr;

class StringVariable extends Variable implements \JsonSerializable {

    public static function getTypeName(): string {
        return "string";
    }

    public function __construct(private string $value) {
    }

    public function getValue(): string {
        return $this->value;
    }

    public function add(Variable $target): StringVariable {
        return new StringVariable($this->getValue().$target);
    }

    public function sub(Variable $target): StringVariable {
        return new StringVariable(str_replace((string)$target, "", $this->getValue()));
    }

    public function mul(Variable $target): StringVariable {
        if ($target instanceof NumberVariable) return new StringVariable(str_repeat($this->getValue(), $target->getValue()));

        throw new UnsupportedCalculationException();
    }

    public function toNBTTag(): Tag {
        return new StringTag((string)$this->value);
    }

    public function jsonSerialize(): array {
        return [
            "type" => static::getTypeName(),
            "value" => $this->getValue(),
        ];
    }

    public static function registerProperties(string $class = self::class): void {
        self::registerMethod(
            $class, "length", new DummyVariable(NumberVariable::class),
            fn(string $value) => new NumberVariable(mb_strlen($value)),
        );
        self::registerMethod(
            $class, "toLowerCase", new DummyVariable(StringVariable::class),
            fn(string $value) => new StringVariable(mb_strtolower($value)),
            aliases: ["lowercase"],
        );
        self::registerProperty(
            $class, "lowercase", new DummyVariable(StringVariable::class),
            fn(string $value) => new StringVariable(mb_strtolower($value)),
        );
        self::registerMethod(
            $class, "toUpperCase", new DummyVariable(StringVariable::class),
            fn(string $value) => new StringVariable(mb_strtoupper($value)),
            aliases: ["uppercase"],
        );
        self::registerProperty(
            $class, "uppercase", new DummyVariable(StringVariable::class),
            fn(string $value) => new StringVariable(mb_strtoupper($value)),
        );
        self::registerMethod(
            $class, "substring", new DummyVariable(StringVariable::class),
            fn(string $value, array $param) => new StringVariable(mb_substr($value, $param[0], $param[1] ?? null)),
        );
    }
}
