<?php

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\ObjectVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use function array_map;

class InventoryVariable extends ObjectVariable {

    public function __construct(Inventory $value, ?string $str = null) {
        parent::__construct($value, $str);
    }

    public function getValueFromIndex(string $index): ?Variable {
        $inventory = $this->getInventory();
        return match ($index) {
            "all" => new ListVariable(array_values(array_map(fn(Item $item) => new ItemVariable($item), $inventory->getContents()))),
            "size" => new NumberVariable($inventory->getSize()),
            default => new ItemVariable($inventory->getItem((int)$index)),
        };
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    public function getInventory(): Inventory {
        return $this->getValue();
    }

    public function __toString(): string {
        $value = $this->getInventory();
        $names = explode("\\", $value::class);
        return $names[array_key_last($names)];
    }
}