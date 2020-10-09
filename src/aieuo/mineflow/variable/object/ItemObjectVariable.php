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

    public function __construct(Item $value, string $name = "", ?string $str = null) {
        parent::__construct($value, $name, $str ?? ($value->getId().":".$value->getDamage()." x".$value->getCount()));
    }

    public function getValueFromIndex(string $index): ?Variable {
        $item = $this->getItem();
        switch ($index) {
            case "name":
                $variable = new StringVariable($item->getName(), "name");
                break;
            case "id":
                $variable = new NumberVariable($item->getId(), "id");
                break;
            case "damage":
                $variable = new NumberVariable($item->getDamage(), "damage");
                break;
            case "count":
                $variable = new NumberVariable($item->getCount(), "count");
                break;
            case "lore":
                $variable = new ListVariable(array_map(function (string $lore) {
                    return new StringVariable($lore);
                }, $item->getLore()), "lore");
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

    public static function getValuesDummy(string $name): array {
        return array_merge(parent::getValuesDummy($name), [
            new DummyVariable($name.".name", DummyVariable::STRING),
            new DummyVariable($name.".id", DummyVariable::NUMBER),
            new DummyVariable($name.".damage", DummyVariable::NUMBER),
            new DummyVariable($name.".count", DummyVariable::NUMBER),
            new DummyVariable($name.".lore", DummyVariable::LIST),
        ]);
    }
}