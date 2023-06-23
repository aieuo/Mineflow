<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\base;


use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\placeholder\HumanPlaceholder;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\object\HumanVariable;
use JetBrains\PhpStorm\Deprecated;
use pocketmine\entity\Human;
use pocketmine\player\Player;

#[Deprecated]
/**
 * @see HumanPlaceholder
 */
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
        if ($variable instanceof HumanVariable) {
            return $variable->getValue();
        }

        throw new InvalidFlowValueException($this->getName(), Language::get("action.target.not.valid", [["action.target.require.human"], $rawName]));
    }

    public function getOnlineHuman(FlowItemExecutor $source, string $name = ""): Human {
        $human = $this->getHuman($source, $name);
        if ($human instanceof Player and !$human->isOnline()) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.error.player.offline"));
        }
        return $human;
    }
}
