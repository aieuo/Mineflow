<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\ObjectVariable;
use aieuo\mineflow\variable\StringVariable;
use pocketmine\math\Facing;
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

    public function __toString(): string {
        $value = $this->getValue();
        return $value->x.",".$value->y.",".$value->z;
    }

    public static function registerProperties(string $class = self::class): void {
        self::registerProperty(
            $class, "x", new DummyVariable(NumberVariable::class),
            fn(Vector3 $position) => new NumberVariable($position->x),
        );
        self::registerProperty(
            $class, "y", new DummyVariable(NumberVariable::class),
            fn(Vector3 $position) => new NumberVariable($position->y),
        );
        self::registerProperty(
            $class, "z", new DummyVariable(NumberVariable::class),
            fn(Vector3 $position) => new NumberVariable($position->z),
        );
        self::registerProperty(
            $class, "xyz", new DummyVariable(StringVariable::class),
            fn(Vector3 $position) => new StringVariable($position->x.",".$position->y.",".$position->z),
        );
        foreach (["down" => Facing::DOWN, "up" => Facing::UP, "north" => Facing::NORTH, "south" => Facing::SOUTH, "west" => Facing::WEST, "east" => Facing::EAST] as $name => $facing) {
            self::registerProperty(
                $class, $name, new DummyVariable(Vector3Variable::class),
                fn(Vector3 $position) => new Vector3Variable($position->getSide($facing)),
            );
        }
    }
}
