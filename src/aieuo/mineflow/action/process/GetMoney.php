<?php

namespace aieuo\mineflow\action\process;

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
use aieuo\mineflow\action\process\Process;
use aieuo\mineflow\formAPI\element\Toggle;

class GetMoney extends Process {

    protected $id = self::GET_MONEY;

    protected $name = "@action.getMoney.name";
    protected $description = "@action.getMoney.description";
    protected $detail = "action.getMoney.detail";

    protected $category = Categories::CATEGORY_ACTION_MONEY;

    protected $targetRequired = Recipe::TARGET_REQUIRED_PLAYER;

    /** @var string */
    private $targetName = "{target.name}";
    /** @var string */
    private $resultName = "money";

    public function __construct(string $name = "{target.name}", string $result = "money") {
        $this->targetName = $name;
        $this->resultName = $result;
    }

    public function setTargetName(string $name): self {
        $this->targetName = $name;
        return $this;
    }

    public function getTargetName(): string {
        return $this->targetName;
    }

    public function setResultName(string $name): self {
        $this->resultName = $name;
        return $this;
    }

    public function getResultName(): string {
        return $this->resultName;
    }

    public function isDataValid(): bool {
        return !empty($this->getTargetName()) and !empty($this->getResultName());
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getTargetName()]);
    }

    public function execute(?Entity $target, ?Recipe $origin = null): ?bool {
        if (!($target instanceof Player)) return false;

        if (!$this->isDataValid()) {
            $target->sendMessage(Language::get("invalid.contents", [$this->getName()]));
            return false;
        }
        if (!Economy::isPluginLoaded()) {
            $target->sendMessage(TextFormat::RED.Language::get("economy.notfound"));
            return null;
        }
        if (!($origin instanceof Recipe)) {
            $target->sendMessage(Language::get("action.error", [Language::get("action.error.recipe"), $this->getName()]));
            return false;
        }

        $targetName = $origin->replaceVariables($this->getTargetName());
        $resultName = $origin->replaceVariables($this->getResultName());
        $money = Economy::getPlugin()->getMoney($targetName);

        $origin->addVariable(new NumberVariable($resultName, $money));
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []) {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@action.getMoney.form.target", Language::get("form.example", ["aieuo"]), $default[1] ?? $this->getTargetName()),
                new Input("@action.getMoney.form.result", Language::get("form.example", ["money"]), $default[2] ?? $this->getResultName()),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $status = true;
        $errors = [];
        if ($data[1] === "") {
            $status = false;
            $errors[] = ["@form.insufficient", 1];
        }
        if ($data[2] === "") {
            $status = false;
            $errors[] = ["@form.insufficient", 2];
        }
        return ["status" => $status, "contents" => [$data[1], $data[2]], "cancel" => $data[3], "errors" => $errors];
    }

    public function parseFromSaveData(array $content): ?Process {
        if (!isset($content[1])) return null;
        $this->setTargetName($content[0]);
        $this->setResultName($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getTargetName(), $this->getResultName()];
    }
}