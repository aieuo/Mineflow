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

    public function getValueFromIndex(string $index): ?Variable {
        $variable = parent::getValueFromIndex($index);
        if ($variable !== null) return $variable;

        $location = $this->getLocation();
        switch ($index) {
            case "yaw":
                $variable = new NumberVariable($location->yaw);
                break;
            case "pitch":
                $variable = new NumberVariable($location->pitch);
                break;
            default:
                return null;
        }
        return $variable;
    }

    public function getLocation(): Location {
        /** @var Location $value */
        $value = $this->getValue();
        return $value;
    }

    public static function getValuesDummy(): array {
        return array_merge(parent::getValuesDummy(), [
            "yaw" => new DummyVariable(DummyVariable::NUMBER),
            "pitch" => new DummyVariable(DummyVariable::NUMBER),
        ]);
    }

    public function __toString(): string {
        $value = $this->getLocation();
        return $value->x.",".$value->y.",".$value->z.",".$value->level->getFolderName()." (".$value->getYaw().",".$value->getPitch().")";
    }
}