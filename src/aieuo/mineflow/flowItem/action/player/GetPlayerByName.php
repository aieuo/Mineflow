<?php

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\PlayerVariable;
use pocketmine\player\Player;
use pocketmine\Server;

class GetPlayerByName extends FlowItem {

    protected string $name = "action.getPlayer.name";
    protected string $detail = "action.getPlayer.detail";
    protected array $detailDefaultReplace = ["name", "result"];

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    public function __construct(
        private string $playerName = "",
        private string $resultName = "player"
    ) {
        parent::__construct(self::GET_PLAYER, FlowItemCategory::PLAYER);
    }

    public function setPlayerName(string $name): self {
        $this->playerName = $name;
        return $this;
    }

    public function getPlayerName(): string {
        return $this->playerName;
    }

    public function setResultName(string $name): self {
        $this->resultName = $name;
        return $this;
    }

    public function getResultName(): string {
        return $this->resultName;
    }

    public function isDataValid(): bool {
        return $this->getPlayerName() !== "" and !empty($this->getResultName());
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getPlayerName(), $this->getResultName()]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $name = $source->replaceVariables($this->getPlayerName());
        $resultName = $source->replaceVariables($this->getResultName());

        $player = Server::getInstance()->getPlayerExact($name);
        if (!($player instanceof Player)) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.getPlayer.player.notFound"));
        }

        $result = new PlayerVariable($player, $player->getName());
        $source->addVariable($resultName, $result);
        yield true;
        return $this->getResultName();
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ExampleInput("@action.getPlayer.form.target", "aieuo", $this->getPlayerName(), true),
            new ExampleInput("@action.form.resultVariableName", "player", $this->getResultName(), true),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setPlayerName($content[0]);
        $this->setResultName($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getPlayerName(), $this->getResultName()];
    }

    public function getAddingVariables(): array {
        return [
            $this->getResultName() => new DummyVariable(PlayerVariable::class, $this->getPlayerName())
        ];
    }
}
