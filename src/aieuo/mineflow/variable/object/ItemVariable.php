<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\ObjectVariable;
use aieuo\mineflow\variable\StringVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\item\Item;

class ItemVariable extends ObjectVariable {

    public static function getTypeName(): string {
        return "item";
    }

    public function __construct(private Item $item) {
    }

    public function getValue(): Item {
        return $this->item;
    }

    public function getValueFromIndex(string $index): ?Variable {
        $item = $this->getValue();
        return match ($index) {
            "name" => new StringVariable($item->getName()),
            "vanilla_name" => new StringVariable($item->getVanillaName()),
            "custom_name" => new StringVariable($item->getCustomName()),
            "id" => new NumberVariable($item->getId()),
            "damage", "meta" => new NumberVariable($item->getMeta()),
            "count" => new NumberVariable($item->getCount()),
            "lore" => new ListVariable(array_map(fn(string $lore) => new StringVariable($lore), $item->getLore())),
            "block" => new BlockVariable($item->getBlock()),
            default => parent::getValueFromIndex($index),
        };
    }

    public function setItem(Item $item): void {
        $this->item = $item;
    }

    public static function getValuesDummy(): array {
        return array_merge(parent::getValuesDummy(), [
            "name" => new DummyVariable(StringVariable::class),
            "vanilla_name" => new DummyVariable(StringVariable::class),
            "custom_name" => new DummyVariable(StringVariable::class),
            "id" => new DummyVariable(NumberVariable::class),
            "damage" => new DummyVariable(NumberVariable::class),
            "meta" => new DummyVariable(NumberVariable::class),
            "count" => new DummyVariable(NumberVariable::class),
            "lore" => new DummyVariable(ListVariable::class, StringVariable::getTypeName()),
            "block" => new DummyVariable(BlockVariable::class),
        ]);
    }

    public function __toString(): string {
        $item = $this->getValue();
        return "Item[".$item->getName()."] (".$item->getId().":".($item->hasAnyDamageValue() ? "?" : $item->getMeta()).")x".$item->getCount();
    }
}
