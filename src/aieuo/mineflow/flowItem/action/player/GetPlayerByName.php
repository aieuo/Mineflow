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

    private StringArgument $playerName;
    private StringArgument $resultName;

    public function __construct(string $playerName = "", string $resultName = "player") {
        parent::__construct(self::GET_PLAYER, FlowItemCategory::PLAYER);

        $this->setArguments([
            $this->playerName = new StringArgument("name", $playerName, example: "aieuo"),
            $this->resultName = new StringArgument("result", $resultName, "@action.form.resultVariableName", example: "player"),
        ]);
    }

    public function getPlayerName(): StringArgument {
        return $this->playerName;
    }

    public function getResultName(): StringArgument {
        return $this->resultName;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $name = $this->playerName->getString($source);
        $resultName = $this->resultName->getString($source);

        $player = Server::getInstance()->getPlayerExact($name);
        if (!($player instanceof Player)) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.getPlayer.player.notFound"));
        }

        $result = new PlayerVariable($player);
        $source->addVariable($resultName, $result);

        yield Await::ALL;
        return $this->resultName->get();
    }

    public function getAddingVariables(): array {
        return [
            $this->resultName->get() => new DummyVariable(PlayerVariable::class, $this->playerName->get())
        ];
    }
}
