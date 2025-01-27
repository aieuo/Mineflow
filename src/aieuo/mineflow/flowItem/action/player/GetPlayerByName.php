<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\PlayerVariable;
use pocketmine\player\Player;
use pocketmine\Server;
use SOFe\AwaitGenerator\Await;

class GetPlayerByName extends SimpleAction {

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    public function __construct(string $playerName = "", string $resultName = "player") {
        parent::__construct(self::GET_PLAYER, FlowItemCategory::PLAYER);

        $this->setArguments([
            StringArgument::create("name", $playerName)->example("aieuo"),
            StringArgument::create("result", $resultName, "@action.form.resultVariableName")->example("player"),
        ]);
    }

    public function getPlayerName(): StringArgument {
        return $this->getArgument("name");
    }

    public function getResultName(): StringArgument {
        return $this->getArgument("result");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $name = $this->getPlayerName()->getString($source);
        $resultName = $this->getResultName()->getString($source);

        $player = Server::getInstance()->getPlayerExact($name);
        if (!($player instanceof Player)) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.getPlayer.player.notFound"));
        }

        $result = new PlayerVariable($player);
        $source->addVariable($resultName, $result);

        yield Await::ALL;
        return (string)$this->getResultName();
    }

    public function getAddingVariables(): array {
        return [
            (string)$this->getResultName() => new DummyVariable(PlayerVariable::class, (string)$this->getPlayerName())
        ];
    }
}