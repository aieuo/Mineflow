<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\VariableProperty;
use pocketmine\math\Facing;
use pocketmine\world\Position;

class PositionVariable extends Vector3Variable {

    public static function getTypeName(): string {
        return "position";
    }

    public function __construct(Position $value) {
        parent::__construct($value);
    }

    public function __toString(): string {
        /** @var Position $value */
        $value = $this->getValue();
        return $value->x.",".$value->y.",".$value->z.",".$value->world->getFolderName();
    }

    public static function registerProperties(string $class = self::class): void {
        Vector3Variable::registerProperties($class);

        self::registerProperty($class, "world", new VariableProperty(
            new DummyVariable(WorldVariable::class),
            fn(Position $position) => new WorldVariable($position->world),
        ));
        foreach (["down" => Facing::DOWN, "up" => Facing::UP, "north" => Facing::NORTH, "south" => Facing::SOUTH, "west" => Facing::WEST, "east" => Facing::EAST] as $name => $facing) {
            self::registerProperty($class, $name, new VariableProperty(
                new DummyVariable(PositionVariable::class),
                fn(Position $position) => new PositionVariable($position->getSide($facing)),
            ), override: true);
        }
    }
}