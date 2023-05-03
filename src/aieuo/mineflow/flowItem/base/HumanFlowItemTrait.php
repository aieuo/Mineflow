<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\base;


use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\object\HumanObjectVariable;
use pocketmine\entity\Human;
use pocketmine\player\Player;

trait HumanFlowItemTrait {

    /* @var string[] */
    private array $humanVariableNames = [];

    public function getHumanVariableName(string $name = ""): string {
        return $this->humanVariableNames[$name] ?? "";
    }

    public function setHumanVariableName(string $entity, string $name = ""): void {
        $this->humanVariableNames[$name] = $entity;
    }

    public function getHuman(FlowItemExecutor $source, string $name = ""): Human {
        $entity = $source->replaceVariables($rawName = $this->getHumanVariableName($name));

        $variable = $source->getVariable($entity);
        if ($variable instanceof HumanObjectVariable and ($entity = $variable->getHuman())) {
            return $entity;
        }

        throw new InvalidFlowValueException($this->getName(), Language::get("action.target.not.valid", [["action.target.require.human"], $rawName]));
    }

    public function throwIfInvalidHuman(Human $entity, bool $checkOnline = true): void {
        if ($entity instanceof Player and $checkOnline and !$entity->isOnline()) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.error.player.offline"));
        }
    }
}