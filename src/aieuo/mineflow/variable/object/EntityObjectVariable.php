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

        /** @var Entity $entity */
        $entity = $this->getValue();
        switch ($index) {
            case "id":
                $variable = new NumberVariable($entity->getId(), "id");
                break;
            case "nameTag":
                $variable = new StringVariable($entity->getNameTag(), "nameTag");
                break;
            default:
                return null;
        }
        return $variable;
    }
}