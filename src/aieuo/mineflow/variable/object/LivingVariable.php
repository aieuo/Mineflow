<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\BooleanVariable;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\entity\Living;

class LivingVariable extends EntityVariable {

    public static function getTypeName(): string {
        return "living";
    }

    public function __construct(Living $entity) {
        parent::__construct($entity);
    }

    public function getValueFromIndex(string $index): ?Variable {
        /** @var Living $living */
        $living = $this->getValue();
        return match ($index) {
            "armor" => new InventoryVariable($living->getArmorInventory()),
            "sprinting" => new BooleanVariable($living->isSprinting()),
            "sneaking" => new BooleanVariable($living->isSneaking()),
            "gliding" => new BooleanVariable($living->isGliding()),
            "swimming" => new BooleanVariable($living->isSwimming()),
            default => parent::getValueFromIndex($index),
        };
    }

    public static function getValuesDummy(): array {
        return array_merge(parent::getValuesDummy(), [
            "armor" => new DummyVariable(DummyVariable::INVENTORY),
            "sprinting" => new DummyVariable(DummyVariable::BOOLEAN),
            "sneaking" => new DummyVariable(DummyVariable::BOOLEAN),
            "gliding" => new DummyVariable(DummyVariable::BOOLEAN),
            "swimming" => new DummyVariable(DummyVariable::BOOLEAN),
        ]);
    }
}
