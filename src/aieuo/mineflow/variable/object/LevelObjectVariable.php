<?php

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\ObjectVariable;
use aieuo\mineflow\variable\StringVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\level\Level;

class LevelObjectVariable extends ObjectVariable {

    public function __construct(Level $value, string $name = "", ?string $str = null) {
        parent::__construct($value, $name, $str ?? $value->getFolderName());
    }

    public function getValueFromIndex(string $index): ?Variable {
        $level = $this->getLevel();
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

    public function getLevel(): Level {
        /** @var Level $value */
        $value = $this->getValue();
        return $value;
    }
}