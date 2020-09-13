<?php


namespace aieuo\mineflow\flowItem\base;


use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\object\EntityObjectVariable;
use pocketmine\entity\Entity;
use pocketmine\Player;

trait EntityFlowItemTrait {

    /* @var string[] */
    private $entityVariableNames = [];

    public function getEntityVariableName(string $name = ""): string {
        return $this->entityVariableNames[$name] ?? "";
    }

    public function setEntityVariableName(string $entity, string $name = "") {
        $this->entityVariableNames[$name] = $entity;
        return $this;
    }

    public function getEntity(Recipe $origin, string $name = ""): ?Entity {
        $entity = $origin->replaceVariables($this->getEntityVariableName($name));

        $variable = $origin->getVariable($entity);
        if (!($variable instanceof EntityObjectVariable)) return null;
        return $variable->getEntity();
    }

    public function throwIfInvalidEntity(?Entity $entity) {
        if (!($entity instanceof Entity)) {
            throw new \UnexpectedValueException(Language::get("flowItem.target.not.valid", [$this->getName(), ["flowItem.target.require.entity"], $this->getEntityVariableName()]));
        }
        if ($entity instanceof Player and !$entity->isOnline()) {
            throw new \UnexpectedValueException(Language::get("flowItem.error", [$this->getName(), ["flowItem.error.player.offline"]]));
        }
    }
}