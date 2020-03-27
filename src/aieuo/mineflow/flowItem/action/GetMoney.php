<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\utils\Logger;
use pocketmine\utils\TextFormat;
use pocketmine\entity\Entity;
use pocketmine\Player;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Categories;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\economy\Economy;
use aieuo\mineflow\formAPI\element\Toggle;

class GetMoney extends Action {

    protected $id = self::GET_MONEY;

    protected $name = "action.getMoney.name";
    protected $detail = "action.getMoney.detail";
    protected $detailDefaultReplace = ["target", "result"];

    protected $category = Categories::CATEGORY_ACTION_MONEY;

    protected $targetRequired = Recipe::TARGET_REQUIRED_PLAYER;
    protected $returnValueType = self::RETURN_NONE;

    /** @var string */
    private $playerName = "{target.name}";
    /** @var string */
    private $resultName = "money";

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

    public function execute(?Entity $target, Recipe $origin): bool {
        $this->throwIfCannotExecute($target);

        if (!Economy::isPluginLoaded()) {
            throw new \UnexpectedValueException(TextFormat::RED.Language::get("economy.notfound"));
        }

        $targetName = $origin->replaceVariables($this->getPlayerName());
        $resultName = $origin->replaceVariables($this->getResultName());

        $money = Economy::getPlugin()->getMoney($targetName);
        $origin->addVariable(new NumberVariable($money, $resultName));
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@action.money.form.target", Language::get("form.example", ["{target.name}"]), $default[1] ?? $this->getPlayerName()),
                new Input("@action.getMoney.form.result", Language::get("form.example", ["money"]), $default[2] ?? $this->getResultName()),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $errors = [];
        if ($data[1] === "") $data[1] = "{target.name}";
        if ($data[2] === "") {
            $errors[] = ["@form.insufficient", 2];
        }
        return ["status" => empty($errors), "contents" => [$data[1], $data[2]], "cancel" => $data[3], "errors" => $errors];
    }

    public function loadSaveData(array $content): Action {
        if (!isset($content[1])) throw new \OutOfBoundsException();
        $this->setPlayerName($content[0]);
        $this->setResultName($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getPlayerName(), $this->getResultName()];
    }
}