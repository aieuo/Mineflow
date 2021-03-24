<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\PlayerObjectVariable;
use pocketmine\Player;
use pocketmine\Server;

class GetPlayerByName extends FlowItem {

    protected $id = self::GET_PLAYER;

    protected $name = "action.getPlayerByName.name";
    protected $detail = "action.getPlayerByName.detail";
    protected $detailDefaultReplace = ["name", "result"];

    protected $category = Category::PLAYER;
    protected $returnValueType = self::RETURN_VARIABLE_NAME;

    /** @var string */
    private $playerName;
    /** @var string */
    private $resultName;

    public function __construct(string $name = "", string $result = "player") {
        $this->playerName = $name;
        $this->resultName = $result;
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

        $player = Server::getInstance()->getPlayer($name);
        if (!($player instanceof Player)) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.getPlayerByName.player.notFound"));
        }

        $result = new PlayerObjectVariable($player, $player->getName());
        $source->addVariable($resultName, $result);
        yield true;
        return $this->getResultName();
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ExampleInput("@action.getPlayerByName.form.target", "aieuo", $this->getPlayerName(), true),
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
        return [new DummyVariable($this->getResultName(), DummyVariable::PLAYER, $this->getPlayerName())];
    }
}