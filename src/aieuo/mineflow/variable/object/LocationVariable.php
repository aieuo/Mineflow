<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\entity\Location;

class LocationVariable extends PositionVariable {

    public static function getTypeName(): string {
        return "location";
    }

    public function __construct(Location $value) {
        parent::__construct($value);
    }

    public function getValueFromIndex(string $index): ?Variable {
        /** @var Location $location */
        $location = $this->getValue();
        return match ($index) {
            "yaw" => new NumberVariable($location->yaw),
            "pitch" => new NumberVariable($location->pitch),
            "down" => new LocationVariable(Location::fromObject($location->down(1), $location->world, $location->yaw, $location->pitch)),
            "up" => new LocationVariable(Location::fromObject($location->up(1), $location->world, $location->yaw, $location->pitch)),
            "north" => new LocationVariable(Location::fromObject($location->north(1), $location->world, $location->yaw, $location->pitch)),
            "south" => new LocationVariable(Location::fromObject($location->south(1), $location->world, $location->yaw, $location->pitch)),
            "west" => new LocationVariable(Location::fromObject($location->west(1), $location->world, $location->yaw, $location->pitch)),
            "east" => new LocationVariable(Location::fromObject($location->east(1), $location->world, $location->yaw, $location->pitch)),
            default => parent::getValueFromIndex($index),
        };
    }

    public static function getValuesDummy(): array {
        return array_merge(parent::getValuesDummy(), [
            "yaw" => new DummyVariable(NumberVariable::class),
            "pitch" => new DummyVariable(NumberVariable::class),
            "down" => new DummyVariable(LocationVariable::class),
            "up" => new DummyVariable(LocationVariable::class),
            "north" => new DummyVariable(LocationVariable::class),
            "south" => new DummyVariable(LocationVariable::class),
            "west" => new DummyVariable(LocationVariable::class),
            "east" => new DummyVariable(LocationVariable::class),
        ]);
    }

    public function __toString(): string {
        /** @var Location $value */
        $value = $this->getValue();
        return $value->x.",".$value->y.",".$value->z.",".$value->world->getFolderName()." (".$value->getYaw().",".$value->getPitch().")";
    }
}
