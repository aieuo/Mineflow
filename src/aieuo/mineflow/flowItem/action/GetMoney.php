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
    protected $detailDefaultReplace = ["result"];

    protected $category = Categories::CATEGORY_ACTION_MONEY;

    protected $targetRequired = Recipe::TARGET_REQUIRED_PLAYER;

    /** @var string */
    private $resultName = "money";

    public function __construct(string $result = "money") {
        $this->resultName = $result;
    }

    public function setResultName(string $name): self {
        $this->resultName = $name;
        return $this;
    }

    public function getResultName(): string {
        return $this->resultName;
    }

    public function isDataValid(): bool {
        return !empty($this->getResultName());
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getResultName()]);
    }

    public function execute(?Entity $target, Recipe $origin): ?bool {
        if (!$this->canExecute($target)) return null;

        if (!Economy::isPluginLoaded()) {
            Logger::warning(TextFormat::RED.Language::get("economy.notfound"), $target);
            return null;
        }

        $resultName = $origin->replaceVariables($this->getResultName());
        /** @var Player $target */
        $money = Economy::getPlugin()->getMoney($target->getName());

        $origin->addVariable(new NumberVariable($money, $resultName));
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@action.getMoney.form.result", Language::get("form.example", ["money"]), $default[1] ?? $this->getResultName()),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $errors = [];
        if ($data[1] === "") {
            $errors[] = ["@form.insufficient", 1];
        }
        return ["status" => empty($errors), "contents" => [$data[1]], "cancel" => $data[2], "errors" => $errors];
    }

    public function loadSaveData(array $content): ?Action {
        if (!isset($content[0])) return null;
        $this->setResultName($content[0]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getResultName()];
    }
}