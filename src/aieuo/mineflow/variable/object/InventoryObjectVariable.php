<?php

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\ObjectVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\inventory\Inventory;

class InventoryObjectVariable extends ObjectVariable {

    public function __construct(Inventory $value, ?string $str = null) {
        parent::__construct($value, $str);
    }

    public function getValueFromIndex(string $index): ?Variable {
        $inventory = $this->getInventory();
        $item = $inventory->getItem((int)$index);
        return new ItemObjectVariable($item);
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    public function getInventory(): Inventory {
        return $this->getValue();
    }

    public function __toString(): string {
        $value = $this->getInventory();
        return "Inventory ({$value->getName()})";
    }
}