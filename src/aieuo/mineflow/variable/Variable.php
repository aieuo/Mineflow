<?php

namespace aieuo\mineflow\variable;

use aieuo\mineflow\exception\UnsupportedCalculationException;
use aieuo\mineflow\flowItem\FlowItemExecutor;

abstract class Variable {

    public const DUMMY = -1;
    public const STRING = 0;
    public const NUMBER = 1;
    public const LIST = 2;
    public const MAP = 3;
    public const OBJECT = 4;
    public const BOOLEAN = 5;
    public const NULL = 6;

    /** @var string|int|Variable[]|object */
    protected $value;

    public int $type;

    public static function create($value, int $type = self::STRING): ?self {
        return match ($type) {
            self::STRING => new StringVariable((string)$value),
            self::NUMBER => new NumberVariable((float)$value),
            self::LIST => new ListVariable($value),
            self::MAP => new MapVariable($value),
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

    /**
     * @param mixed $value
     */
    public function __construct($value) {
        $this->value = $value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value): void {
        $this->value = $value;
    }

    /**
     * @return string|int|Variable[]|object|bool
     */
    public function getValue() {
        return $this->value;
    }

    public function getValueFromIndex(string $index): ?Variable {
        return null;
    }

    public function callMethod(string $name, array $parameters = []): ?Variable {
        return null;
    }

    public function getType(): int {
        return $this->type;
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