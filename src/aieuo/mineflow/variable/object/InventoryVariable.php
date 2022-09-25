<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\ObjectVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use function array_map;

class InventoryVariable extends ObjectVariable {

    public static function getTypeName(): string {
        return "inventory";
    }

    public function __construct(private Inventory $inventory) {
    }

    public function getValue(): Inventory {
        return $this->inventory;
    }

    public function getValueFromIndex(string $index): ?Variable {
        $inventory = $this->getValue();
        return match ($index) {
            "all" => new ListVariable(array_values(array_map(fn(Item $item) => new ItemVariable($item), $inventory->getContents()))),
            "size" => new NumberVariable($inventory->getSize()),
            default => new ItemVariable($inventory->getItem((int)$index)),
        };
    }

    public function __toString(): string {
        $value = $this->getValue();
        $names = explode("\\", $value::class);
        return $names[array_key_last($names)];
    }
}
