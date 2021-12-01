<?php

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\ObjectVariable;
use aieuo\mineflow\variable\StringVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\item\Item;

class ItemObjectVariable extends ObjectVariable {

    public function __construct(Item $value, ?string $str = null) {
        parent::__construct($value, $str);
    }

    public function getProperty(string $name): ?Variable {
        $item = $this->getItem();
        return match ($name) {
            "name" => new StringVariable($item->getName()),
            "id" => new NumberVariable($item->getId()),
            "damage" => new NumberVariable($item->getMeta()),
            "count" => new NumberVariable($item->getCount()),
            "lore" => new ListVariable(array_map(fn(string $lore) => new StringVariable($lore), $item->getLore())),
            default => null,
        };
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    public function getItem(): Item {
        return $this->getValue();
    }

    public function setItem(Item $item): void  {
        $this->setValue($item);
    }

    public static function getTypeName(): string {
        return "item";
    }

    public static function getValuesDummy(): array {
        return [
            "name" => new DummyVariable(StringVariable::class),
            "id" => new DummyVariable(NumberVariable::class),
            "damage" => new DummyVariable(NumberVariable::class),
            "count" => new DummyVariable(NumberVariable::class),
            "lore" => new DummyVariable(ListVariable::class, StringVariable::getTypeName()),
        ];
    }

    public function __toString(): string {
        $item = $this->getItem();
        return "Item[".$item->getName()."] (".$item->getId().":".($item->hasAnyDamageValue() ? "?" : $item->getMeta()).")x".$item->getCount();
    }
}