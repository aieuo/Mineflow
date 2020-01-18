<?php

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\StringVariable;
use aieuo\mineflow\variable\Variable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\ObjectVariable;
use pocketmine\level\Position;

class PositionObjectVariable extends ObjectVariable {
    public function getValueFromIndex(string $index): ?Variable {
        /** @var Position $position */
        $position = $this->getValue();
        switch ($index) {
            case "x":
                $variable = new NumberVariable($position->x, "x");
                break;
            case "y":
                $variable = new NumberVariable($position->y, "y");
                break;
            case "z":
                $variable = new NumberVariable($position->z, "z");
                break;
            case "xyz":
                $variable = new StringVariable($position->x.",".$position->y.",".$position->z, "xyz");
                break;
            case "level":
                $variable = new LevelObjectVariable($position->level, "level", $position->level->getFolderName());
                break;
            default:
                return null;
        }
        return $variable;
    }
}