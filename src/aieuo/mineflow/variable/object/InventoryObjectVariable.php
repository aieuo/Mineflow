<?php

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\ObjectVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use function array_map;

class InventoryObjectVariable extends ObjectVariable {

    public function __construct(Inventory $value, ?string $str = null) {
        parent::__construct($value, $str);
    }

    public function getValueFromIndex(string $index): ?Variable {
        $inventory = $this->getInventory();
        return match ($index) {
            "all" => new ListVariable(array_values(array_map(fn(Item $item) => new ItemObjectVariable($item), $inventory->getContents(true)))),
            "items" => new ListVariable(array_values(array_map(fn(Item $item) => new ItemObjectVariable($item), $inventory->getContents(false)))),
            "size" => new NumberVariable($inventory->getSize()),
            default => new ItemObjectVariable($inventory->getItem((int)$index)),
        };
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    public function getInventory(): Inventory {
        return $this->getValue();
    }

    public function __toString(): string {
        return (string)$this->getValueFromIndex("all");
    }
}
