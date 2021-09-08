<?php

namespace aieuo\mineflow\variable;

use aieuo\mineflow\exception\UnsupportedCalculationException;
use aieuo\mineflow\flowItem\FlowItemExecutor;

abstract class Variable {

    public static function create($value, int|string $type): ?self {
        return match ($type) {
            0, StringVariable::getTypeName() => new StringVariable((string)$value),
            1, NumberVariable::getTypeName() => new NumberVariable((float)$value),
            2, ListVariable::getTypeName() => new ListVariable($value),
            3, MapVariable::getTypeName() => new MapVariable($value),
            5, BooleanVariable::getTypeName() => new BooleanVariable($value),
            default => null,
        };
    }

    public static function fromArray(array $data): ?self {
        if (!isset($data["value"]) or !isset($data["type"])) return null;

        if (!is_array($data["value"])) return self::create($data["value"], $data["type"]);

        $values = [];
        foreach ($data["value"] as $key => $value) {
            if (!is_array($value)) continue;

            $var = self::fromArray($value);
            if ($var === null) continue;

            $values[$key] = $var;
        }
        return self::create($values, $data["type"]);
    }

    abstract public static function getTypeName(): string;

    abstract public function getValue(): mixed;

    public function getValueFromIndex(string $index): ?Variable {
        return null;
    }

    public function callMethod(string $name, array $parameters = []): ?Variable {
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
}