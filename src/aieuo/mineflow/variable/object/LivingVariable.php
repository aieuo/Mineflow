<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\BooleanVariable;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\VariableProperty;
use pocketmine\entity\Living;

class LivingVariable extends EntityVariable {

    public static function getTypeName(): string {
        return "living";
    }

    public function __construct(Living $entity) {
        parent::__construct($entity);
    }

    public static function registerProperties(string $class = self::class): void {
        EntityVariable::registerProperties($class);

        self::registerProperty($class, "armor", new VariableProperty(
            new DummyVariable(InventoryVariable::class),
            fn(Living $living) => new InventoryVariable($living->getArmorInventory()),
        ));
        self::registerProperty($class, "sprinting", new VariableProperty(
            new DummyVariable(BooleanVariable::class),
            fn(Living $living) => new BooleanVariable($living->isSprinting()),
        ));
        self::registerProperty($class, "sneaking", new VariableProperty(
            new DummyVariable(BooleanVariable::class),
            fn(Living $living) => new BooleanVariable($living->isSneaking()),
        ));
        self::registerProperty($class, "gliding", new VariableProperty(
            new DummyVariable(BooleanVariable::class),
            fn(Living $living) => new BooleanVariable($living->isGliding()),
        ));
        self::registerProperty($class, "swimming", new VariableProperty(
            new DummyVariable(BooleanVariable::class),
            fn(Living $living) => new BooleanVariable($living->isSwimming()),
        ));
    }
}