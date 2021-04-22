<?php

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\StringVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\entity\Entity;

class EntityObjectVariable extends PositionObjectVariable {

    public function getValueFromIndex(string $index): ?Variable {
        $variable = parent::getValueFromIndex($index);
        if ($variable !== null) return $variable;

        $entity = $this->getEntity();
        switch ($index) {
            case "id":
                $variable = new NumberVariable($entity->getId());
                break;
            case "nameTag":
                $variable = new StringVariable($entity->getNameTag());
                break;
            case "health":
                $variable = new NumberVariable($entity->getHealth());
                break;
            case "maxHealth":
                $variable = new NumberVariable($entity->getMaxHealth());
                break;
            case "yaw":
                $variable = new NumberVariable($entity->getYaw());
                break;
            case "pitch":
                $variable = new NumberVariable($entity->getPitch());
                break;
            case "direction":
                $variable = new NumberVariable($entity->getDirection());
                break;
            default:
                return null;
        }
        return $variable;
    }

    public function getEntity(): Entity {
        /** @var Entity $value */
        $value = $this->getValue();
        return $value;
    }

    public static function getValuesDummy(): array {
        return array_merge(parent::getValuesDummy(), [
            "id" => new DummyVariable(DummyVariable::NUMBER),
            "nameTag" => new DummyVariable(DummyVariable::STRING),
            "health" => new DummyVariable(DummyVariable::NUMBER),
            "maxHealth" => new DummyVariable(DummyVariable::NUMBER),
            "yaw" => new DummyVariable(DummyVariable::NUMBER),
            "pitch" => new DummyVariable(DummyVariable::NUMBER),
            "direction" => new DummyVariable(DummyVariable::NUMBER),
        ]);
    }

    public function __toString(): string {
        $value = $this->getEntity();
        return $value->getNameTag();
    }
}