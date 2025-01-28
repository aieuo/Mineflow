<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\VariableProperty;
use pocketmine\entity\Location;
use pocketmine\math\Facing;

class LocationVariable extends PositionVariable {

    public static function getTypeName(): string {
        return "location";
    }

    public function __construct(Location $value) {
        parent::__construct($value);
    }

    public function __toString(): string {
        /** @var Location $value */
        $value = $this->getValue();
        return $value->x.",".$value->y.",".$value->z.",".$value->world->getFolderName()." (".$value->getYaw().",".$value->getPitch().")";
    }

    public static function registerProperties(string $class = self::class): void {
        PositionVariable::registerProperties($class);

        self::registerProperty($class, "yaw", new VariableProperty(
            new DummyVariable(NumberVariable::class),
            fn(Location $location) => new NumberVariable($location->yaw),
        ));
        self::registerProperty($class, "pitch", new VariableProperty(
            new DummyVariable(NumberVariable::class),
            fn(Location $location) => new NumberVariable($location->pitch),
        ));
        foreach (["down" => Facing::DOWN, "up" => Facing::UP, "north" => Facing::NORTH, "south" => Facing::SOUTH, "west" => Facing::WEST, "east" => Facing::EAST] as $name => $facing) {
            self::registerProperty($class, $name, new VariableProperty(
                new DummyVariable(LocationVariable::class),
                fn(Location $location) => new LocationVariable(Location::fromObject($location->getSide($facing), $location->world, $location->yaw, $location->pitch)),
            ), override: true);
        }
    }
}