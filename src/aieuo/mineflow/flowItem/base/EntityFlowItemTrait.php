<?php


namespace aieuo\mineflow\flowItem\base;


use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItemExecutor;
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

    public function setEntityVariableName(string $entity, string $name = ""): void {
        $this->entityVariableNames[$name] = $entity;
    }

    public function getEntity(FlowItemExecutor $source, string $name = ""): Entity {
        $entity = $source->replaceVariables($rawName = $this->getEntityVariableName($name));

        $variable = $source->getVariable($entity);
        if ($variable instanceof EntityObjectVariable and ($entity = $variable->getEntity())) {
            return $entity;
        }

        throw new InvalidFlowValueException($this->getName(), Language::get("action.target.not.valid", [["action.target.require.entity"], $rawName]));
    }

    public function throwIfInvalidEntity(Entity $entity, bool $checkOnline = true): void {
        if ($entity instanceof Player and $checkOnline and !$entity->isOnline()) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.error.player.offline"));
        }
    }
}