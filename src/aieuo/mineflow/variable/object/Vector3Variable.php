<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\ObjectVariable;
use aieuo\mineflow\variable\StringVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\math\Vector3;

class Vector3Variable extends ObjectVariable {

    public static function getTypeName(): string {
        return "vector3";
    }

    public function __construct(private Vector3 $vector3) {
    }

    public function getValue(): Vector3 {
        return $this->vector3;
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

    public static function getValuesDummy(): array {
        return array_merge(parent::getValuesDummy(), [
            "x" => new DummyVariable(NumberVariable::class),
            "y" => new DummyVariable(NumberVariable::class),
            "z" => new DummyVariable(NumberVariable::class),
            "xyz" => new DummyVariable(StringVariable::class),
            "down" => new DummyVariable(Vector3Variable::class),
            "up" => new DummyVariable(Vector3Variable::class),
            "north" => new DummyVariable(Vector3Variable::class),
            "south" => new DummyVariable(Vector3Variable::class),
            "west" => new DummyVariable(Vector3Variable::class),
            "east" => new DummyVariable(Vector3Variable::class),
        ]);
    }

    public function __toString(): string {
        $value = $this->getValue();
        return $value->x.",".$value->y.",".$value->z;
    }
}
