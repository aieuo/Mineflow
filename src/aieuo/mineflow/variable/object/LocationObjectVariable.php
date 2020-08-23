<?php

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\Variable;
use aieuo\mineflow\variable\NumberVariable;
use pocketmine\level\Location;

class LocationObjectVariable extends PositionObjectVariable {

    public function __construct(Location $value, string $name = "", ?string $str = null) {
        parent::__construct($value, $name, $str ?? ($value->x.",".$value->y.",".$value->z.",".$value->level->getFolderName()." (".$value->getYaw().",".$value->getPitch().")"));
    }

    public function getValueFromIndex(string $index): ?Variable {
        $variable = parent::getValueFromIndex($index);
        if ($variable !== null) return $variable;

        $location = $this->getLocation();
        switch ($index) {
            case "yaw":
                $variable = new NumberVariable($location->yaw, "yaw");
                break;
            case "pitch":
                $variable = new NumberVariable($location->pitch, "pitch");
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
}