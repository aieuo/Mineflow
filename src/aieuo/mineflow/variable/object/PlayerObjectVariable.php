<?php

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\StringVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\Player;

class PlayerObjectVariable extends EntityObjectVariable {
    public function getValueFromIndex(string $index): ?Variable {
        $variable = parent::getValueFromIndex($index);
        if ($variable !== null) return $variable;

        $player = $this->getPlayer();
        switch ($index) {
            case "name":
                $variable = new StringVariable($player->getName(), "name");
                break;
            case "hand":
                $variable = new ItemObjectVariable($player->getInventory()->getItemInHand(), "hand");
                break;
            default:
                return null;
        }
        return $variable;
    }

    public function getPlayer(): Player {
        /** @var Player $value */
        $value = $this->getValue();
        return $value;
    }
}