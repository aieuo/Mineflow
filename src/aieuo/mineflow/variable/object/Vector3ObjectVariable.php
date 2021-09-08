<?php

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\ObjectVariable;
use aieuo\mineflow\variable\StringVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\math\Vector3;

class Vector3ObjectVariable extends ObjectVariable {

    public function __construct(Vector3 $value, ?string $str = null) {
        parent::__construct($value, $str);
    }

    public function getProperty(string $name): ?Variable {
        $position = $this->getVector3();
        switch ($name) {
            case "x":
                return new NumberVariable($position->x);
            case "y":
                return new NumberVariable($position->y);
            case "z":
                return new NumberVariable($position->z);
            case "xyz":
                return new StringVariable($position->x.",".$position->y.",".$position->z);
            default:
                return null;
        }
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    public function getVector3(): Vector3 {
        return $this->getValue();
    }

    public static function getTypeName(): string {
        return "vector3";
    }

    public static function getValuesDummy(): array {
        return [
            "x" => new DummyVariable(NumberVariable::class),
            "y" => new DummyVariable(NumberVariable::class),
            "z" => new DummyVariable(NumberVariable::class),
            "xyz" => new DummyVariable(StringVariable::class),
        ];
    }

    public function __toString(): string {
        $value = $this->getVector3();
        return $value->x.",".$value->y.",".$value->z;
    }
}