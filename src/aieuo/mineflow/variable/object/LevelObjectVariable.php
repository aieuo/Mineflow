<?php

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\StringVariable;
use aieuo\mineflow\variable\Variable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\ObjectVariable;
use pocketmine\level\Level;

class LevelObjectVariable extends ObjectVariable {
    public function getValueFromIndex(string $index): ?Variable {
        /** @var Level $level */
        $level = $this->getValue();
        switch ($index) {
            case "name":
                $variable = new StringVariable($level->getName(), "name");
                break;
            case "folderName":
                $variable = new StringVariable($level->getFolderName(), "folderName");
                break;
            case "id":
                $variable = new NumberVariable($level->getId(), "id");
                break;
            default:
                return null;
        }
        return $variable;
    }
}