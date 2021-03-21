<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\economy\Economy;
use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use pocketmine\utils\TextFormat;

class GetMoney extends FlowItem {

    protected $id = self::GET_MONEY;

    protected $name = "action.getMoney.name";
    protected $detail = "action.getMoney.detail";
    protected $detailDefaultReplace = ["target", "result"];

    protected $category = Category::PLUGIN;

    protected $returnValueType = self::RETURN_VARIABLE_VALUE;

    /** @var string */
    private $playerName;
    /** @var string */
    private $resultName;

    public function __construct(string $name = "{target.name}", string $result = "money") {
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
        return $this->getPlayerName() !== "" and $this->getResultName() !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getPlayerName(), $this->getResultName()]);
    }

    public function execute(Recipe $source): \Generator {
        $this->throwIfCannotExecute();

        if (!Economy::isPluginLoaded()) {
            throw new InvalidFlowValueException(TextFormat::RED.Language::get("economy.notfound"));
        }

        $targetName = $source->replaceVariables($this->getPlayerName());
        $resultName = $source->replaceVariables($this->getResultName());

        $money = Economy::getPlugin()->getMoney($targetName);
        $source->addVariable(new NumberVariable($money, $resultName));
        yield true;
        return $money;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ExampleInput("@action.money.form.target", "{target.name}", $this->getPlayerName(), true),
            new ExampleInput("@action.form.resultVariableName", "money", $this->getResultName(), true),
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
        return [new DummyVariable($this->getResultName(), DummyVariable::NUMBER)];
    }
}