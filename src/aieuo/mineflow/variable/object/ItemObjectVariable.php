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
                $variable = new StringVariable($item->getName());
                break;
            case "id":
                $variable = new NumberVariable($item->getId());
                break;
            case "damage":
                $variable = new NumberVariable($item->getDamage());
                break;
            case "count":
                $variable = new NumberVariable($item->getCount());
                break;
            case "lore":
                $variable = new ListVariable(array_map(function (string $lore) {
                    return new StringVariable($lore);
                }, $item->getLore()));
                break;
            default:
                return null;
        }
        return $variable;
    }

    public function getItem(): Item {
        /** @var Item $value */
        $value = $this->getValue();
        return $value;
    }

    public static function getValuesDummy(): array {
        return array_merge(parent::getValuesDummy(), [
            "name" => new DummyVariable(DummyVariable::STRING),
            "id" => new DummyVariable(DummyVariable::NUMBER),
            "damage" => new DummyVariable(DummyVariable::NUMBER),
            "count" => new DummyVariable(DummyVariable::NUMBER),
            "lore" => new DummyVariable(DummyVariable::LIST),
        ]);
    }

    public function __toString(): string {
        $item = $this->getItem();
        return "Item[".$item->getName()."] (".$item->getId().":".($item->hasAnyDamageValue() ? "?" : $item->getDamage()).")x".$item->getCount();
    }
}