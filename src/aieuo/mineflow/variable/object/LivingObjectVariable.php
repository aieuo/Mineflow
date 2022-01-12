<?php

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\BoolVariable;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\StringVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\entity\Entity;
use pocketmine\entity\EntityFactory;
use pocketmine\entity\Human;
use pocketmine\entity\Living;
use pocketmine\player\Player;

class LivingObjectVariable extends EntityObjectVariable {

    public function __construct(Living $entity, ?string $str = null) {
        parent::__construct($entity, $str);
    }

    public function getValueFromIndex(string $index): ?Variable {
        $living = $this->getLiving();
        return match ($index) {
            "armor" => new InventoryObjectVariable($living->getArmorInventory()),
            "sprinting" => new BoolVariable($living->isSprinting()),
            "sneaking" => new BoolVariable($living->isSneaking()),
            "gliding" => new BoolVariable($living->isGliding()),
            "swimming" => new BoolVariable($living->isSwimming()),
            default => parent::getValueFromIndex($index),
        };
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    public function getLiving(): Living {
        return $this->getEntity();
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