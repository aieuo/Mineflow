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

    public function getValueFromIndex(string $index): ?Variable {
        $item = $this->getItem();
        switch ($index) {
            case "name":
                return new StringVariable($item->getName());
            case "id":
                return new NumberVariable($item->getId());
            case "damage":
                return new NumberVariable($item->getDamage());
            case "count":
                return new NumberVariable($item->getCount());
            case "lore":
                return new ListVariable(array_map(fn(string $lore) => new StringVariable($lore), $item->getLore()));
            default:
                return null;
        }
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    public function getItem(): Item {
        return $this->getValue();
    }

    public static function getValuesDummy(): array {
        return array_merge(parent::getValuesDummy(), [
            "name" => new DummyVariable(DummyVariable::STRING),
            "id" => new DummyVariable(DummyVariable::NUMBER),
            "damage" => new DummyVariable(DummyVariable::NUMBER),
            "count" => new DummyVariable(DummyVariable::NUMBER),
            "lore" => new DummyVariable(DummyVariable::LIST, DummyVariable::STRING),
        ]);
    }

    public function __toString(): string {
        $item = $this->getItem();
        return "Item[".$item->getName()."] (".$item->getId().":".($item->hasAnyDamageValue() ? "?" : $item->getDamage()).")x".$item->getCount();
    }
}