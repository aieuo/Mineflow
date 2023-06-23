<?php

declare(strict_types=1);


namespace aieuo\mineflow\flowItem\base;


use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\object\PlayerVariable;
use JetBrains\PhpStorm\Deprecated;
use pocketmine\player\Player;

#[Deprecated]
/**
 * @see PlayerPlaceholder
 */
trait PlayerFlowItemTrait {

    /* @var string[] */
    private array $playerVariableNames = [];

    public function getPlayerVariableName(string $name = ""): string {
        return $this->playerVariableNames[$name] ?? "";
    }

    public function setPlayerVariableName(string $player, string $name = ""): void {
        $this->playerVariableNames[$name] = $player;
    }

    public function getPlayer(FlowItemExecutor $source, string $name = ""): Player {
        $player = $source->replaceVariables($rawName = $this->getPlayerVariableName($name));

        $variable = $source->getVariable($player);
        if ($variable instanceof PlayerVariable) {
            return $variable->getValue();
        }

        throw new InvalidFlowValueException($this->getName(), Language::get("action.target.not.valid", [["action.target.require.player"], $rawName]));
    }

    public function getOnlinePlayer(FlowItemExecutor $source, string $name = ""): Player {
        $player = $this->getPlayer($source, $name);
        if (!$player->isOnline()) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.error.player.offline"));
        }
        return $player;
    }
}
