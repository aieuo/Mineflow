<?php

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\entity\Human;

class HumanVariable extends LivingVariable {

    public function __construct(Human $value, ?string $str = null) {
        parent::__construct($value, $str ?? $value->getName());
    }

    public function getValueFromIndex(string $index): ?Variable {
        $human = $this->getHuman();
        return match ($index) {
            "hand" => new ItemVariable($human->getInventory()->getItemInHand()),
            "food" => new NumberVariable($human->getHungerManager()->getFood()),
            "xp" => new NumberVariable($human->getXpManager()->getCurrentTotalXp()),
            "xp_level" => new NumberVariable($human->getXpManager()->getXpLevel()),
            "xp_progress" => new NumberVariable($human->getXpManager()->getXpProgress()),
            "inventory" => new InventoryVariable($human->getInventory()),
            default => parent::getValueFromIndex($index),
        };
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    public function getHuman(): Human {
        return $this->getEntity();
    }

    public static function getValuesDummy(): array {
        return array_merge(parent::getValuesDummy(), [
            "hand" => new DummyVariable(DummyVariable::ITEM),
            "food" => new DummyVariable(DummyVariable::NUMBER),
            "xp" => new DummyVariable(DummyVariable::NUMBER),
            "xp_level" => new DummyVariable(DummyVariable::NUMBER),
            "xp_progress" => new DummyVariable(DummyVariable::NUMBER),
            "inventory" => new DummyVariable(DummyVariable::INVENTORY),
        ]);
    }
}