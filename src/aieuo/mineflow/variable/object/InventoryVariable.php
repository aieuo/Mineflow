<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\IteratorVariable;
use aieuo\mineflow\variable\IteratorVariableTrait;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\ObjectVariable;
use aieuo\mineflow\variable\Variable;
use aieuo\mineflow\variable\VariableProperty;
use pocketmine\block\VanillaBlocks;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use Traversable;
use function array_map;
use function array_values;

class InventoryVariable extends ObjectVariable implements IteratorVariable {
    use IteratorVariableTrait;

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

    public function getIterator(): Traversable {
        foreach ($this->getValue()->getContents() as $i => $item) {
            yield $i => new ItemVariable($item);
        }
    }

    public function hasKey(int|string $key): bool {
        return $this->getValue()->getItem((int)$key)->getTypeId() !== VanillaBlocks::AIR()->getTypeId();
    }

    public function setValueAt(int|string $key, Variable $value): void {
        if (!($value instanceof ItemVariable)) return;

        $this->getValue()->setItem((int)$key, $value->getValue());
    }

    public function removeValueAt(int|string $index): void {
        $this->getValue()->clear((int)$index);
    }

    public function __toString(): string {
        return (string)$this->getProperty("all");
    }

    public static function registerProperties(string $class = self::class): void {
        self::registerProperty($class, "all", new VariableProperty(
            new DummyVariable(ListVariable::class, ItemVariable::getTypeName()),
            fn(Inventory $inventory) => new ListVariable(array_values(array_map(fn(Item $item) => new ItemVariable($item), $inventory->getContents()))),
        ));
        self::registerProperty($class, "size", new VariableProperty(
            new DummyVariable(NumberVariable::class),
            fn(Inventory $inventory) => new NumberVariable($inventory->getSize()),
        ));

        self::registerIteratorMethods($class);
    }
}