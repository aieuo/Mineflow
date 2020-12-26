<?php

namespace aieuo\mineflow\variable;

abstract class Variable {

    public const DUMMY = -1;
    public const STRING = 0;
    public const NUMBER = 1;
    public const LIST = 2;
    public const MAP = 3;
    public const OBJECT = 4;

    /** @var string 変数の名前 */
    protected $name;
    /** @var string|int|Variable[]|object 変数の値 */
    protected $value;
    /** @var int 変数の型 */
    public $type;

    /**
     * @param mixed $value
     * @param string $name
     * @param int $type
     * @return self|null
     */
    public static function create($value, string $name = "", int $type = self::STRING): ?self {
        $variable = null;
        switch ($type) {
            case self::STRING:
                $variable = StringVariable::fromArray(["name" => $name, "value" => (string)$value]);
                break;
            case self::NUMBER:
                $variable = NumberVariable::fromArray(["name" => $name, "value" => (float)$value]);
                break;
            case self::LIST:
                $variable = ListVariable::fromArray(["name" => $name, "value" => $value]);
                break;
            case self::MAP:
                $variable = MapVariable::fromArray(["name" => $name, "value" => $value]);
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
     * @return string|int|Variable[]|object
     */
    public function getValue() {
        return $this->value;
    }

    public function getType(): int {
        return $this->type;
    }

    public function isSavable(): bool {
        return true;
    }

    public function __toString(): string {
        return (string)$this->getValue();
    }

    abstract public function toStringVariable(): StringVariable;

    abstract public static function fromArray(array $data): ?Variable;
}