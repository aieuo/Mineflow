<?php

declare(strict_types=1);


namespace aieuo\mineflow\flowItem\base;


use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\object\EntityVariable;
use pocketmine\entity\Entity;
use pocketmine\player\Player;

trait EntityFlowItemTrait {

    /* @var string[] */
    private array $entityVariableNames = [];

    public function getEntityVariableName(string $name = ""): string {
        return $this->entityVariableNames[$name] ?? "";
    }

    public function setEntityVariableName(string $entity, string $name = ""): void {
        $this->entityVariableNames[$name] = $entity;
    }

    public function getEntity(FlowItemExecutor $source, string $name = ""): Entity {
        $entity = $source->replaceVariables($rawName = $this->getEntityVariableName($name));

        $variable = $source->getVariable($entity);
        if ($variable instanceof EntityVariable) {
            return $variable->getValue();
        }

        throw new InvalidFlowValueException($this->getName(), Language::get("action.target.not.valid", [["action.target.require.entity"], $rawName]));
    }

    public function getOnlineEntity(FlowItemExecutor $source, string $name = ""): Entity {
        $entity = $this->getEntity($source, $name);
        if ($entity instanceof Player and !$entity->isOnline()) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.error.player.offline"));
        }
        return $entity;
    }
}
