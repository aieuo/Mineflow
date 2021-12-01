<?php

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\BooleanVariable;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\StringVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\entity\Entity;

class EntityObjectVariable extends PositionObjectVariable {

    public function getProperty(string $name): ?Variable {
        $variable = parent::getProperty($name);
        if ($variable !== null) return $variable;

        $entity = $this->getEntity();
        switch ($name) {
            case "id":
                return new NumberVariable($entity->getId());
            case "saveId":
                try {
                    return new StringVariable($entity->getSaveId());
                } catch (\InvalidStateException) {
                    return new StringVariable("");
                }
            case "nameTag":
                return new StringVariable($entity->getNameTag());
            case "health":
                return new NumberVariable($entity->getHealth());
            case "maxHealth":
                return new NumberVariable($entity->getMaxHealth());
            case "yaw":
                return new NumberVariable($entity->getYaw());
            case "pitch":
                return new NumberVariable($entity->getPitch());
            case "direction":
                return new NumberVariable($entity->getDirection());
            case "onGround":
                return new BooleanVariable($entity->isOnGround());
            default:
                return null;
        }
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    public function getEntity(): Entity {
        return $this->getValue();
    }

    public static function getTypeName(): string {
        return "entity";
    }

    public static function getValuesDummy(): array {
        return array_merge(parent::getValuesDummy(), [
            "id" => new DummyVariable(NumberVariable::class),
            "saveId" => new DummyVariable(StringVariable::class),
            "nameTag" => new DummyVariable(StringVariable::class),
            "health" => new DummyVariable(NumberVariable::class),
            "maxHealth" => new DummyVariable(NumberVariable::class),
            "yaw" => new DummyVariable(NumberVariable::class),
            "pitch" => new DummyVariable(NumberVariable::class),
            "direction" => new DummyVariable(NumberVariable::class),
        ]);
    }

    public function __toString(): string {
        $value = $this->getEntity();
        return $value->getNameTag();
    }
}