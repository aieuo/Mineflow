<?php

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\ObjectVariable;
use aieuo\mineflow\variable\StringVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\item\Item;

class ItemObjectVariable extends ObjectVariable {
    public function getValueFromIndex(string $index): ?Variable {
        /** @var Item $item */
        $item = $this->getValue();
        switch ($index) {
            case "name":
                $variable = new StringVariable($item->getName(), "name");
                break;
            case "id":
                $variable = new NumberVariable($item->getId(), "id");
                break;
            case "damage":
                $variable = new NumberVariable($item->getDamage(), "damage");
                break;
            case "count":
                $variable = new NumberVariable($item->getCount(), "count");
                break;
            default:
                return null;
        }
        return $variable;
    }
}