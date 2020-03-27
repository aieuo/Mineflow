<?php

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\StringVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\block\Block;

class BlockObjectVariable extends PositionObjectVariable {
    public function getValueFromIndex(string $index): ?Variable {
        $variable = parent::getValueFromIndex($index);
        if ($variable !== null) return $variable;

        $block = $this->getBlock();
        switch ($index) {
            case "name":
                $variable = new StringVariable($block->getName(), "name");
                break;
            case "id":
                $variable = new NumberVariable($block->getId(), "id");
                break;
            case "damage":
                $variable = new NumberVariable($block->getDamage(), "damage");
                break;
            default:
                return null;
        }
        return $variable;
    }

    public function getBlock(): Block {
        /** @var Block $value */
        $value = $this->getValue();
        return $value;
    }
}