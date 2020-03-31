<?php

namespace aieuo\mineflow\variable;

abstract class Variable {

    const STRING = 0;
    const NUMBER = 1;
    const LIST = 2;
    const MAP = 3;
    const OBJECT = 4;

    /** @var string 変数の名前 */
    protected $name;
    /** @var string|int|array|object 変数の値 */
    protected $value;
    /** @var int 変数の型 */
    public $type;
    /** @var array 型の一覧 */
    public $typeNames = ["string", "number", "list", "map", "object"];

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

    public function setValue($value): void {
        $this->value = $value;
    }

    /**
     * @return string|int|array|object
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

    public function __toString() {
        return (string)$this->getValue();
    }

    abstract public function toStringVariable(): StringVariable;

    abstract public static function fromArray(array $data): ?Variable;
}