<?php


namespace aieuo\mineflow\flowItem\base;


use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\object\EntityObjectVariable;
use pocketmine\entity\Entity;
use pocketmine\Player;

trait EntityFlowItemTrait {

    /* @var string */
    private $entityVariableName = "target";

    public function getEntityVariableName(): String {
        return $this->entityVariableName;
    }

    public function setEntityVariableName(string $name) {
        $this->entityVariableName = $name;
        return $this;
    }

    public function getEntity(Recipe $origin): ?Entity {
        $name = $origin->replaceVariables($this->getEntityVariableName());

        $variable = $origin->getVariable($name);
        if (!($variable instanceof EntityObjectVariable)) return null;
        return $variable->getEntity();
    }

    public function throwIfInvalidEntity(?Entity $entity) {
        if (!($entity instanceof Entity)) {
            throw new \UnexpectedValueException(Language::get("flowItem.target.not.valid", [$this->getName(), ["flowItem.target.require.entity"]]));
        }
        if ($entity instanceof Player and !$entity->isOnline()) {
            throw new \UnexpectedValueException(Language::get("flowItem.error", [$this->getName(), ["flowItem.error.player.offline"]]));
        }
    }
}