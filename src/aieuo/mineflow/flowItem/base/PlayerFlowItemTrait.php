<?php


namespace aieuo\mineflow\flowItem\base;


use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\object\PlayerObjectVariable;
use pocketmine\Player;

trait PlayerFlowItemTrait {

    /* @var string[] */
    private $playerVariableNames = [];

    public function getPlayerVariableName(string $name = ""): string {
        return $this->playerVariableNames[$name] ?? "";
    }

    public function setPlayerVariableName(string $player, string $name = ""): void {
        $this->playerVariableNames[$name] = $player;
    }

    public function getPlayer(Recipe $origin, string $name = ""): ?Player {
        $player = $origin->replaceVariables($this->getPlayerVariableName($name));

        $variable = $origin->getVariable($player);
        if (!($variable instanceof PlayerObjectVariable)) return null;
        return $variable->getPlayer();
    }

    public function throwIfInvalidPlayer(?Player $player, bool $allowOffline = false): void {
        if (!($player instanceof Player)) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.target.not.valid", [["action.target.require.player"], $this->getPlayerVariableName()]));
        }
        if (!$allowOffline and !$player->isOnline()) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.error.player.offline"));
        }
    }
}