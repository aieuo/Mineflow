<?php

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\ObjectVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\inventory\Inventory;
use function array_key_last;
use function explode;

class InventoryObjectVariable extends ObjectVariable {

    public function __construct(Inventory $value, ?string $str = null) {
        parent::__construct($value, $str);
    }

    public function getProperty(string $name): ?Variable {
        $inventory = $this->getInventory();
        $item = $inventory->getItem((int)$name);
        return new ItemObjectVariable($item);
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    public function getInventory(): Inventory {
        return $this->getValue();
    }

    public static function getTypeName(): string {
        return "inventory";
    }

    public static function getValuesDummy(): array {
        return [];
    }

    public function __toString(): string {
        $value = $this->getInventory();
        $names = explode("\\", $value::class);
        return "Inventory (size={$names[array_key_last($names)]})";
    }
}