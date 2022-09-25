<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\entity\Human;

class HumanVariable extends LivingVariable {

    public static function getTypeName(): string {
        return "human";
    }

    public function __construct(Human $value) {
        parent::__construct($value);
    }

    public function __toString(): string {
        /** @var Human $value */
        $value = $this->getValue();
        return $value->getName();
    }

    public function getValueFromIndex(string $index): ?Variable {
        /** @var Human $human */
        $human = $this->getValue();
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

    public static function getValuesDummy(): array {
        return array_merge(parent::getValuesDummy(), [
            "hand" => new DummyVariable(ItemVariable::class),
            "food" => new DummyVariable(NumberVariable::class),
            "xp" => new DummyVariable(NumberVariable::class),
            "xp_level" => new DummyVariable(NumberVariable::class),
            "xp_progress" => new DummyVariable(NumberVariable::class),
            "inventory" => new DummyVariable(InventoryVariable::class),
        ]);
    }
}
