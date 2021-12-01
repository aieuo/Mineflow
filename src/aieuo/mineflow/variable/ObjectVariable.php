<?php

namespace aieuo\mineflow\variable;

use aieuo\mineflow\Main;

abstract class ObjectVariable extends Variable {

    private ?string $showString;

    public function __construct(private object $value, ?string $str = null) {
        $this->showString = $str;
    }

    public function getValue(): object {
        return $this->value;
    }

    protected function setValue(object $value): void {
        $this->value = $value;
    }

    abstract public function getProperty(string $name): ?Variable;

    public function getShowString(): ?string {
        return $this->showString;
    }

    public function getValueFromIndex(string $index): ?Variable {
        $property = $this->getProperty($index);
        if ($property !== null) return $property;

        $property = $this->getAdditionalProperty($index);
        if ($property !== null) $property($this->getValue());

        return null;
    }

    public function __toString(): string {
        if (!empty($this->showString)) return (string)$this->showString;
        if (method_exists($this->getValue(), "__toString")) {
            $str = (string)$this->getValue();
        } else {
            $str = get_class($this->getValue());
        }
        return $str;
    }

    /**
     * @return array<string, DummyVariable>
     */
    abstract public static function getValuesDummy(): array;

    public static function addAdditionalProperties(string $name, callable $property, DummyVariable $dummyVariable): void {
        Main::getVariableHelper()->addObjectVariableProperty(static::class, $name, $property, $dummyVariable);
    }

    public static function getAdditionalProperties(): array {
        return Main::getVariableHelper()->getAdditionalObjectVariableProperties(static::class);
    }

    public static function getDummyAdditionalProperties(): array {
        return Main::getVariableHelper()->getDummyAdditionalObjectVariableProperties(static::class);
    }

    public function getAdditionalProperty(string $name): ?callable {
        return self::getAdditionalProperties()[$name] ?? null;
    }
}