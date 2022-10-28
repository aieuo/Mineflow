<?php

namespace aieuo\mineflow\variable;

use aieuo\mineflow\exception\UnsupportedCalculationException;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use JetBrains\PhpStorm\Deprecated;
use pocketmine\nbt\tag\Tag;

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

    #[Deprecated(replacement: "VariableDeserializer::deserialize(%parameter0%)")]
    public static function fromArray(array $data): ?self {
        return VariableDeserializer::deserialize($data);
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

    public function add($target): Variable {
        throw new UnsupportedCalculationException();
    }

    public function sub($target): Variable {
        throw new UnsupportedCalculationException();
    }

    public function mul($target): Variable {
        throw new UnsupportedCalculationException();
    }

    public function div($target): Variable {
        throw new UnsupportedCalculationException();
    }

    public function map(string|array|Variable $target, ?FlowItemExecutor $executor = null, array $variables = [], bool $global = false): ListVariable {
        throw new UnsupportedCalculationException();
    }

    public function toNBTTag(): Tag {
        throw new \UnexpectedValueException();
    }
}
