<?php

namespace aieuo\mineflow\variable;

use function array_merge;

/**
 * @template T of aieuo\mineflow\variable\Variable
 */
class DummyVariable extends Variable {

    public const SHORT_VARIABLE_TYPES = [
        StringVariable::class => "string",
        NumberVariable::class => "number",
        ListVariable::class => "list",
        MapVariable::class => "map",
        BooleanVariable::class => "boolean",
    ];

    public static function getTypeName(): string {
        return "dummy";
    }

    /**
     * @param class-string<T> $valueClass
     * @param string $description
     */
    public function __construct(private string $valueClass = "", private string $description = "") {
    }

    public function getDescription(): string {
        return $this->description;
    }

    public function getValueClass(): string {
        return $this->valueClass;
    }

    public function getValueType(): string {
        /** @var string|Variable|ObjectVariable $class */
        $class = $this->getValueClass();
        if ($this->isObjectVariableType()) {
            return $class::getTypeName();
        }

        return self::SHORT_VARIABLE_TYPES[$class] ?? "unknown";
    }

    public function toStringVariable(): StringVariable {
        return new StringVariable($this->getValue());
    }

    public function getValueFromIndex(string $index): ?Variable {
        $variables = $this->getObjectValuesDummy();
        return $variables[$index] ?? null;
    }

    /**
     * @return array<string, DummyVariable>
     */
    public function getObjectValuesDummy(): array {
        /** @var string|Variable|ObjectVariable $class */
        $class = $this->getValueClass();
        if ($this->isObjectVariableType()) {
            return array_merge($class::getValuesDummy(), $class::getDummyAdditionalProperties());
        }

        return [];
    }

    public function isObjectVariableType(): bool {
        return is_subclass_of($this->getValueClass(), ObjectVariable::class);
    }

    public function getValue(): mixed {
        return null;
    }
}