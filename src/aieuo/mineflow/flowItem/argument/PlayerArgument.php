<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\argument;

use aieuo\mineflow\exception\InvalidPlaceholderValueException;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\Element;
use aieuo\mineflow\formAPI\element\mineflow\PlayerVariableDropdown;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\object\PlayerVariable;
use pocketmine\player\Player;

class PlayerArgument extends ObjectVariableArgument {

    public function __construct(string $name, string $value = "", string $description = null, bool $optional = false) {
        parent::__construct($name, $value, $description ?? "@action.form.target.player", $optional);
    }

    /**
     * @throws InvalidPlaceholderValueException
     */
    public function getPlayer(FlowItemExecutor $executor): Player {
        $player = $executor->replaceVariables($this->getVariableName());

        $variable = $executor->getVariable($player);
        if ($variable instanceof PlayerVariable) {
            return $variable->getValue();
        }

        throw new InvalidPlaceholderValueException(Language::get("action.target.not.valid", [["action.target.require.player"], $this->getVariableName()]));
    }

    /**
     * @throws InvalidPlaceholderValueException
     */
    public function getOnlinePlayer(FlowItemExecutor $executor): Player {
        $player = $this->getPlayer($executor);
        if (!$player->isOnline()) {
            throw new InvalidPlaceholderValueException(Language::get("action.error.player.offline"));
        }
        return $player;
    }

    public function createFormElement(array $variables): Element {
        return new PlayerVariableDropdown($variables, $this->getVariableName(), $this->getDescription(), $this->isOptional());
    }
}
