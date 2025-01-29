<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable;

use aieuo\mineflow\exception\UnsupportedCalculationException;
use pocketmine\nbt\tag\Tag;
use function array_map;

abstract class Variable {

    /** @var array<class-string<Variable>, array<string, VariableProperty>> */
    private static array $properties = [];
    /** @var array<class-string<Variable>, array<string, VariableMethod>> */
    private static array $methods = [];

    public static function create($value, int|string $type): ?self {
        return match ($type) {
            0, StringVariable::getTypeName() => new StringVariable((string)$value),
            1, NumberVariable::getTypeName() => new NumberVariable((float)$value),
            2, ListVariable::getTypeName() => new ListVariable($value),
            3, MapVariable::getTypeName() => new MapVariable($value),
            5, BooleanVariable::getTypeName() => new BooleanVariable($value),
            6, NullVariable::getTypeName() => new NullVariable(),
            default => null,
        };
    }

    abstract public static function getTypeName(): string;

    abstract public function getValue(): mixed;

    protected function getValueFromIndex(string $index): ?Variable {
        return null;
    }

    public function __toString(): string {
        return (string)$this->getValue();
    }

    public function add(Variable $target): Variable {
        throw new UnsupportedCalculationException();
    }

    public function sub(Variable $target): Variable {
        throw new UnsupportedCalculationException();
    }

    public function mul(Variable $target): Variable {
        throw new UnsupportedCalculationException();
    }

    public function div(Variable $target): Variable {
        throw new UnsupportedCalculationException();
    }

    final public function getProperty(string $name): ?Variable {
        return self::getPropertyObject($name)?->get($this) ?? $this->getValueFromIndex($name);
    }

    final public function callMethod(string $name, array $parameters = []): ?Variable {
        return self::getMethod($name)?->call($this, $parameters);
    }

    /**
     * @param class-string $class
     * @param string $name
     * @param VariableProperty $property
     * @param bool $override
     * @param string[] $aliases
     * @return void
     */
    public static function registerProperty(string $class, string $name, VariableProperty $property, bool $override = false, array $aliases = []): void {
        if (!$override and isset(self::$properties[$class][$name])) {
            throw new \InvalidArgumentException("Variable property ".$name." of ".$class." is already registered.");
        }

        self::$properties[$class][$name] = $property;

        foreach ($aliases as $alias) {
            self::registerProperty($class, $alias, $property, $override);
        }
    }

    public static function getPropertyObject(string $name): ?VariableProperty {
        return self::$properties[static::class][$name] ?? null;
    }

    public static function getProperties(): array {
        return self::$properties[static::class] ?? [];
    }

    /**
     * @return DummyVariable[]
     */
    public static function getPropertyTypes(): array {
        return array_map(fn(VariableProperty $p) => $p->getType(), self::getProperties());
    }

    /**
     * @param class-string $class
     * @param string $name
     * @param VariableMethod $method
     * @param bool $override
     * @param string[] $aliases
     * @return void
     */
    public static function registerMethod(string $class, string $name, VariableMethod $method, bool $override = false, array $aliases = []): void {
        if (!$override and isset(self::$methods[$class][$name])) {
            throw new \InvalidArgumentException("Variable method ".$name." of ".$class." is already registered.");
        }

        self::$methods[$class][$name] = $method;

        foreach ($aliases as $alias) {
            self::registerMethod($class, $alias, $method, $override);
        }
    }

    public static function getMethod(string $name): ?VariableMethod {
        return self::$methods[static::class][$name] ?? null;
    }

    public static function getMethods(): array {
        return self::$methods[static::class] ?? [];
    }

    /**
     * @return DummyVariable[]
     */
    public static function getMethodTypes(): array {
        return array_map(fn(VariableMethod $m) => $m->getType(), self::getMethods());
    }

    public function toNBTTag(): Tag {
        throw new \UnexpectedValueException();
    }
}