<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable;

use function assert;
use function is_subclass_of;

/**
 * @template T of Variable
 */
class DummyVariable extends Variable {

    public static function getTypeName(): string {
        return "dummy";
    }

    /**
     * @param class-string<T> $valueClass
     * @param string $description
     */
    public function __construct(private string $valueClass = "", private string $description = "") {
        assert(is_subclass_of($this->valueClass, Variable::class));
    }

    public function getDescription(): string {
        return $this->description;
    }

    public function getValueClass(): string {
        return $this->valueClass;
    }

    public function getValueType(): string {
        /** @var string|Variable $class */
        $class = $this->getValueClass();
        return $class::getTypeName();
    }

    protected function getValueFromIndex(string $index): ?Variable {
        $variables = $this->getObjectValuesDummy();
        return $variables[$index] ?? null;
    }

    /**
     * @return array<string, DummyVariable>
     */
    public function getObjectValuesDummy(): array {
        /** @var string|Variable|ObjectVariable $class */
        $class = $this->getValueClass();
        return $class::getPropertyTypes();
    }

    public function isObjectVariableType(): bool {
        return is_subclass_of($this->getValueClass(), ObjectVariable::class);
    }

    public function getValue(): mixed {
        return null;
    }
}