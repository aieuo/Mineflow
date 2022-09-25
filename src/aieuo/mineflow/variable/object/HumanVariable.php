<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
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

    public static function registerProperties(string $class = self::class): void {
        LivingVariable::registerProperties($class);

        self::registerProperty(
            $class, "hand", new DummyVariable(ItemVariable::class),
            fn(Human $human) => new ItemVariable($human->getInventory()->getItemInHand()),
        );
        self::registerProperty(
            $class, "food", new DummyVariable(NumberVariable::class),
            fn(Human $human) => new NumberVariable($human->getHungerManager()->getFood()),
        );
        self::registerProperty(
            $class, "xp", new DummyVariable(NumberVariable::class),
            fn(Human $human) => new NumberVariable($human->getXpManager()->getCurrentTotalXp()),
        );
        self::registerProperty(
            $class, "xp_level", new DummyVariable(NumberVariable::class),
            fn(Human $human) => new NumberVariable($human->getXpManager()->getXpLevel()),
        );
        self::registerProperty(
            $class, "xp_progress", new DummyVariable(NumberVariable::class),
            fn(Human $human) => new NumberVariable($human->getXpManager()->getXpProgress()),
        );
        self::registerProperty(
            $class, "inventory", new DummyVariable(InventoryVariable::class),
            fn(Human $human) => new InventoryVariable($human->getInventory()),
        );
    }
}
