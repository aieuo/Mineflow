<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\Mineflow;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\MapVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\ObjectVariable;
use aieuo\mineflow\variable\StringVariable;
use pocketmine\item\Item;
use function array_map;

class ItemVariable extends ObjectVariable {

    public static function getTypeName(): string {
        return "item";
    }

    public function __construct(private Item $item) {
    }

    public function getValue(): Item {
        return $this->item;
    }

    public function setItem(Item $item): void {
        $this->item = $item;
    }

    public function __toString(): string {
        $item = $this->getValue();
        return "Item[".$item->getName()."] (".$item->getId().":".($item->hasAnyDamageValue() ? "?" : $item->getMeta()).")x".$item->getCount();
    }

    public static function registerProperties(string $class = self::class): void {
        self::registerProperty(
            $class, "name", new DummyVariable(StringVariable::class),
            fn(Item $item) => new StringVariable($item->getName()),
        );
        self::registerProperty(
            $class, "vanilla_name", new DummyVariable(StringVariable::class),
            fn(Item $item) => new StringVariable($item->getVanillaName()),
        );
        self::registerProperty(
            $class, "custom_name", new DummyVariable(StringVariable::class),
            fn(Item $item) => new StringVariable($item->getCustomName()),
        );
        self::registerProperty(
            $class, "id", new DummyVariable(NumberVariable::class),
            fn(Item $item) => new NumberVariable($item->getId()),
        );
        self::registerProperty(
            $class, "damage", new DummyVariable(NumberVariable::class),
            fn(Item $item) => new NumberVariable($item->getMeta()),
            aliases: ["meta"],
        );
        self::registerProperty(
            $class, "count", new DummyVariable(NumberVariable::class),
            fn(Item $item) => new NumberVariable($item->getCount()),
        );
        self::registerProperty(
            $class, "lore", new DummyVariable(ListVariable::class, StringVariable::getTypeName()),
            fn(Item $item) => new ListVariable(array_map(fn(string $lore) => new StringVariable($lore), $item->getLore())),
        );
        self::registerProperty(
            $class, "block", new DummyVariable(BlockVariable::class),
            fn(Item $item) => new BlockVariable($item->getBlock()),
        );
        self::registerProperty(
            $class, "tag", new DummyVariable(MapVariable::class),
            fn(Item $item) => Mineflow::getVariableHelper()->tagToVariable($item->getNamedTag()),
        );
    }
}
