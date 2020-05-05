<?php

namespace aieuo\mineflow\variable\object;

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
                $variable = new NumberVariable($entity->getId(), "id");
                break;
            case "nameTag":
                $variable = new StringVariable($entity->getNameTag(), "nameTag");
                break;
            case "health":
                $variable = new NumberVariable($entity->getHealth(), "health");
                break;
            case "maxHealth":
                $variable = new NumberVariable($entity->getMaxHealth(), "maxHealth");
                break;
            case "yaw":
                $variable = new NumberVariable($entity->getYaw(), "yaw");
                break;
            case "pitch":
                $variable = new NumberVariable($entity->getPitch(), "pitch");
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
}