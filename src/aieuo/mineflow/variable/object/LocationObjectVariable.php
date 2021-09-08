<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\level\Location;

class LocationObjectVariable extends PositionObjectVariable {

    public function __construct(Location $value, ?string $str = null) {
        parent::__construct($value, $str);
    }

    public function getProperty(string $name): ?Variable {
        $variable = parent::getProperty($name);
        if ($variable !== null) return $variable;

        $location = $this->getLocation();
        return match ($name) {
            "yaw" => new NumberVariable($location->yaw),
            "pitch" => new NumberVariable($location->pitch),
            default => null,
        };
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    public function getLocation(): Location {
        return $this->getValue();
    }

    public static function getTypeName(): string {
        return "location";
    }

    public static function getValuesDummy(): array {
        return array_merge(parent::getValuesDummy(), [
            "yaw" => new DummyVariable(NumberVariable::class),
            "pitch" => new DummyVariable(NumberVariable::class),
        ]);
    }

    public function __toString(): string {
        $value = $this->getLocation();
        return $value->x.",".$value->y.",".$value->z.",".$value->level->getFolderName()." (".$value->getYaw().",".$value->getPitch().")";
    }
}