<?php

namespace aieuo\mineflow\variable;

use aieuo\mineflow\exception\UnsupportedCalculationException;

abstract class Variable {

    public const DUMMY = -1;
    public const STRING = 0;
    public const NUMBER = 1;
    public const LIST = 2;
    public const MAP = 3;
    public const OBJECT = 4;
    public const BOOLEAN = 5;

    /** @var string */
    protected $name;
    /** @var string|int|Variable[]|object */
    protected $value;
    /** @var int */
    public $type;

    public static function create($value, string $name = "", int $type = self::STRING): ?self {
        $variable = null;
        switch ($type) {
            case self::STRING:
                $variable = new StringVariable((string)$value, $name);
                break;
            case self::NUMBER:
                $variable = new NumberVariable((float)$value, $name);
                break;
            case self::LIST:
                $variable = new ListVariable($value, $name);
                break;
            case self::MAP:
                $variable = new MapVariable($value, $name);
                break;
            default:
                return null;
        }
        return $variable;
    }

    /**
     * @param mixed $value
     * @param string $name
     */
    public function __construct($value, string $name = "") {
        $this->value = $value;
        $this->name = $name;
    }

    public function setName(string $name): void {
        $this->name = $name;
    }

    public function getName(): string {
        return $this->name;
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
}