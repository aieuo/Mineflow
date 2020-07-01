<?php


namespace aieuo\mineflow\flowItem\base;


use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\object\PlayerObjectVariable;
use pocketmine\Player;

trait PlayerFlowItemTrait {

    /* @var string */
    private $playerVariableName = "target";

    public function getPlayerVariableName(): string {
        return $this->playerVariableName;
    }

    public function setPlayerVariableName(string $name) {
        $this->playerVariableName = $name;
        return $this;
    }

    public function getPlayer(Recipe $origin): ?Player {
        $name = $origin->replaceVariables($this->getPlayerVariableName());

        $variable = $origin->getVariable($name);
        if (!($variable instanceof PlayerObjectVariable)) return null;
        return $variable->getPlayer();
    }

    public function throwIfInvalidPlayer(?Player $player, bool $allowOffline = false) {
        if (!($player instanceof Player)) {
            throw new \UnexpectedValueException(Language::get("flowItem.target.not.valid", [$this->getName(), ["flowItem.target.require.player"], $this->getPlayerVariableName()]));
        }
        if (!$allowOffline and !$player->isOnline()) {
            throw new \UnexpectedValueException(Language::get("flowItem.error", [$this->getName(), ["flowItem.error.player.offline"]]));
        }
    }
}