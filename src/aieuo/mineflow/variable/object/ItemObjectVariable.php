<?php

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\Main;
use aieuo\mineflow\Mineflow;
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
        $helper = Mineflow::getVariableHelper();
        return match ($index) {
            "name" => new StringVariable($item->getName()),
            "vanilla_name" => new StringVariable($item->getVanillaName()),
            "custom_name" => new StringVariable($item->getCustomName()),
            "id" => new NumberVariable($item->getId()),
            "damage", "meta" => new NumberVariable($item->getMeta()),
            "count" => new NumberVariable($item->getCount()),
            "lore" => new ListVariable(array_map(fn(string $lore) => new StringVariable($lore), $item->getLore())),
            "block" => new BlockObjectVariable($item->getBlock()),
            "tag" => $helper->tagToVariable($item->getNamedTag()),
            default => parent::getValueFromIndex($index),
        };
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    public function getItem(): Item {
        return $this->getValue();
    }

    public function setItem(Item $item): void  {
        $this->setValue($item);
    }

    public static function getValuesDummy(): array {
        return array_merge(parent::getValuesDummy(), [
            "name" => new DummyVariable(DummyVariable::STRING),
            "vanilla_name" => new DummyVariable(DummyVariable::STRING),
            "custom_name" => new DummyVariable(DummyVariable::STRING),
            "id" => new DummyVariable(DummyVariable::NUMBER),
            "damage" => new DummyVariable(DummyVariable::NUMBER),
            "meta" => new DummyVariable(DummyVariable::NUMBER),
            "count" => new DummyVariable(DummyVariable::NUMBER),
            "lore" => new DummyVariable(DummyVariable::LIST, DummyVariable::STRING),
            "block" => new DummyVariable(DummyVariable::BLOCK),
        ]);
    }

    public function __toString(): string {
        $item = $this->getItem();
        return "Item[".$item->getName()."] (".$item->getId().":".($item->hasAnyDamageValue() ? "?" : $item->getMeta()).")x".$item->getCount();
    }
}
