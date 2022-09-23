<?php

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\ObjectVariable;
use aieuo\mineflow\variable\StringVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\math\Vector3;

class Vector3Variable extends ObjectVariable {

    public function __construct(Vector3 $value, ?string $str = null) {
        parent::__construct($value, $str);
    }

    public function getValueFromIndex(string $index): ?Variable {
        $position = $this->getVector3();
        return match ($index) {
            "x" => new NumberVariable($position->x),
            "y" => new NumberVariable($position->y),
            "z" => new NumberVariable($position->z),
            "xyz" => new StringVariable($position->x.",".$position->y.",".$position->z),
            "down" => new Vector3Variable($position->down(1)),
            "up" => new Vector3Variable($position->up(1)),
            "north" => new Vector3Variable($position->north(1)),
            "south" => new Vector3Variable($position->south(1)),
            "west" => new Vector3Variable($position->west(1)),
            "east" => new Vector3Variable($position->east(1)),
            default => parent::getValueFromIndex($index),
        };
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    public function getVector3(): Vector3 {
        return $this->getValue();
    }

    public static function getValuesDummy(): array {
        return array_merge(parent::getValuesDummy(), [
            "x" => new DummyVariable(DummyVariable::NUMBER),
            "y" => new DummyVariable(DummyVariable::NUMBER),
            "z" => new DummyVariable(DummyVariable::NUMBER),
            "xyz" => new DummyVariable(DummyVariable::STRING),
            "down" => new DummyVariable(DummyVariable::VECTOR3),
            "up" => new DummyVariable(DummyVariable::VECTOR3),
            "north" => new DummyVariable(DummyVariable::VECTOR3),
            "south" => new DummyVariable(DummyVariable::VECTOR3),
            "west" => new DummyVariable(DummyVariable::VECTOR3),
            "east" => new DummyVariable(DummyVariable::VECTOR3),
        ]);
    }

    public function __toString(): string {
        $value = $this->getVector3();
        return $value->x.",".$value->y.",".$value->z;
    }
}