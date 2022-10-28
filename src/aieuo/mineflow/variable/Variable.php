<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable;

use aieuo\mineflow\exception\UnsupportedCalculationException;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use pocketmine\nbt\tag\Tag;

abstract class Variable {

    /** @var array<class-string<ObjectVariable>, array<string, callable>> */
    private static array $properties = [];
    /** @var array<class-string<ObjectVariable>, array<string, DummyVariable>> */
    private static array $propertyTypes = [];

    /** @var array<class-string<ObjectVariable>, array<string, callable>> */
    private static array $methods = [];
    /** @var array<class-string<ObjectVariable>, array<string, DummyVariable>> */
    private static array $methodTypes = [];

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

    public function map(string|array|Variable $target, ?FlowItemExecutor $executor = null, array $variables = [], bool $global = false): ListVariable {
        throw new UnsupportedCalculationException();
    }

    final public function getProperty(string $name): ?Variable {
        $property = self::getProperties()[$name] ?? null;
        if ($property !== null) {
            return $property($this->getValue());
        }

        return $this->getValueFromIndex($name);
    }

    final public function callMethod(string $name, array $parameters = []): ?Variable {
        $method = self::getMethods()[$name] ?? null;
        if ($method !== null) {
            return $method($this->getValue(), ...$parameters);
        }

        return null;
    }

    /**
     * @param class-string $class
     * @param string $name
     * @param DummyVariable $dummyVariable
     * @param callable $property
     * @param bool $override
     * @param string[] $aliases
     * @return void
     */
    public static function registerProperty(string $class, string $name, DummyVariable $dummyVariable, callable $property, bool $override = false, array $aliases = []): void {
        if (!$override and isset(self::$properties[$class][$name])) {
            throw new \InvalidArgumentException("Variable property ".$name." of ".$class." is already registered.");
        }

        self::$properties[$class][$name] = $property;
        self::$propertyTypes[$class][$name] = $dummyVariable;

        foreach ($aliases as $alias) {
            self::registerProperty($class, $alias, $dummyVariable, $property, $override);
        }
    }

    public static function getProperties(): array {
        return self::$properties[static::class] ?? [];
    }

    public static function getValuesDummy(): array {
        return self::$propertyTypes[static::class] ?? [];
    }

    /**
     * @param class-string $class
     * @param string $name
     * @param DummyVariable $dummyVariable
     * @param callable $method
     * @param bool $override
     * @param string[] $aliases
     * @return void
     */
    public static function registerMethod(string $class, string $name, DummyVariable $dummyVariable, callable $method, bool $override = false, array $aliases = []): void {
        if (!$override and isset(self::$methods[$class][$name])) {
            throw new \InvalidArgumentException("Variable method ".$name." of ".$class." is already registered.");
        }

        self::$methods[$class][$name] = $method;
        self::$methodTypes[$class][$name] = $dummyVariable;

        foreach ($aliases as $alias) {
            self::registerMethod($class, $alias, $dummyVariable, $method, $override);
        }
    }

    public static function getMethods(): array {
        return self::$methods[static::class] ?? [];
    }

    public static function getMethodTypes(): array {
        return self::$methodTypes[static::class] ?? [];
    }

    public function toNBTTag(): Tag {
        throw new \UnexpectedValueException();
    }
}
