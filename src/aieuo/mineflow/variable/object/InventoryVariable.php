<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\ObjectVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use function array_map;
use function array_values;

class InventoryVariable extends ObjectVariable {

    public static function getTypeName(): string {
        return "inventory";
    }

    public function __construct(private Inventory $inventory) {
    }

    protected function getValueFromIndex(string $index): ?Variable {
        $inventory = $this->getValue();
        return new ItemVariable($inventory->getItem((int)$index));
    }

    public function getValue(): Inventory {
        return $this->inventory;
    }

    public function __toString(): string {
        return (string)$this->getProperty("all");
    }

    public static function registerProperties(string $class = self::class): void {
        self::registerProperty(
            $class, "all", new DummyVariable(ListVariable::class, ItemVariable::getTypeName()),
            fn(Inventory $inventory) => new ListVariable(array_values(array_map(fn(Item $item) => new ItemVariable($item), $inventory->getContents()))),
        );
        self::registerProperty(
            $class, "size", new DummyVariable(NumberVariable::class),
            fn(Inventory $inventory) => new NumberVariable($inventory->getSize()),
        );
    }
}
