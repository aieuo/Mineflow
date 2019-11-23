<?php
namespace aieuo\mineflow\variable;

use aieuo\mineflow\variable\StringVariable;

abstract class Variable {
    const STRING = 0;
    const NUMBER = 1;
    const LIST = 2;
    const MAP = 3;

    /** @var string 変数の名前 */
    protected $name;
    /** @var string|int|array 変数の値 */
    protected $value;
    /** @var int 変数の型 */
    public $type;
    /** @var array 型の一覧 */
    public $typeNames = ["string", "number", "list", "map"];

    public static function create($name, $value, $type = self::STRING) {
        if ($type === self::STRING) {
            $var = new StringVariable($name, $value);
        } elseif ($type === self::NUMBER) {
            $var = new NumberVariable($name, $value);
        } elseif ($type === self::LIST) {
            if (is_array($value)) {
                $var = new ListVariable($name, $value);
            } else {
                $var = ListVariable::fromString($name, (string)$value);
            }
        } elseif ($type === self::MAP) {
            if (is_array($value)) {
                $var = new MapVariable($name, $value);
            } else {
                $var = MapVariable::fromString($name, (string)$value);
            }
        }
        return $var;
    }

    public function __construct($name, $value) {
        $this->name = $name;
        $this->value = $value;
    }

    public function getName(): string {
        return $this->name;
    }

    public function setValue($value) {
        $this->value = $value;
    }

    /**
     * @return string|int|array
     */
    public function getValue() {
        return $this->value;
    }

    public function getType(): int {
        return $this->type;
    }

    /**
     * 変数同士を足す
     * @param Variable $var
     * @param string   $name
     */
    abstract public function addition(Variable $var, string $name = "result"): Variable;

    /**
     * 変数同士を引く
     * @param Variable $var
     * @param string   $name
     */
    abstract public function subtraction(Variable $var, string $name = "result"): Variable;

    /**
     * 変数同士を掛ける
     * @param Variable $var
     * @param string   $name
     */
    abstract public function multiplication(Variable $var, string $name = "result"): Variable;

    /**
     * 変数同士を割る
     * @param Variable $var
     * @param string   $name
     */
    abstract public function division(Variable $var, string $name = "result"): Variable;

    /**
     * 変数同士を割った余り
     * @param Variable $var
     * @param string   $name
     */
    abstract public function modulo(Variable $var, string $name = "result"): Variable;

    abstract public function toStringVariable(): StringVariable;
}