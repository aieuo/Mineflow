<?php

namespace aieuo\mineflow\action\process;

use pocketmine\entity\Entity;
use pocketmine\Server;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Categories;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\action\process\Process;
use aieuo\mineflow\Main;
use aieuo\mineflow\FormAPI\element\Toggle;
use aieuo\mineflow\variable\ListVariable;

class AddListVariable extends Process {

    protected $id = self::ADD_LIST_VARIABLE;

    protected $name = "@action.addListVariable.name";
    protected $description = "@action.addListVariable.description";
    protected $detail = "action.addListVariable.detail";

    protected $category = Categories::CATEGORY_ACTION_VARIABLE;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;

    /** @var string */
    private $variableName;
    /** @var string */
    private $variableValue;
    /** @var bool */
    private $isLocal = true;

    public function __construct(string $name = "", string $value = "", bool $local = true) {
        $this->variableName = $name;
        $this->variableValue = $value;
        $this->isLocal = $local;
    }

    public function setVariableName(string $variableName) {
        $this->variableName = $variableName;
    }

    public function getVariableName(): string {
        return $this->variableName;
    }

    public function setVariableValue(string $variableValue) {
        $this->variableValue = $variableValue;
    }

    public function getVariableValue(): string {
        return $this->variableValue;
    }

    public function isDataValid(): bool {
        return !empty($this->variableName);
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getVariableName(), $this->isLocal ? "local" : "global", $this->getVariableValue()]);
    }

    public function execute(?Entity $target, ?Recipe $origin = null): ?bool {
        if (!$this->isDataValid()) {
            $target->sendMessage(Language::get("invalid.contents", [$this->getName()]));
            return null;
        }

        $helper = Main::getInstance()->getVariableHelper();
        $name = $this->getVariableName();
        $value = $this->getVariableValue();
        $isRecipe = $origin instanceof Recipe;

        if ($isRecipe) {
            $name = $origin->replaceVariables($name);
            $value = $origin->replaceVariables($value);
        } elseif (!$this->isLocal) {
            if ($target instanceof Player) $target->sendMessage(Language::get("action.error", [$this->getName(), Language::get("action.error.recipe")]));
            else Server::getInstance()->getLogger()->info(Language::get("action.error", [$this->getName(), Language::get("action.error.recipe")]));
            return null;
        }

        if ($value === "" and $this->isLocal) {
            $origin->addVariable(new ListVariable($name, []));
            return true;
        } elseif ($value === "") {
            $helper->add(new ListVariable($name, []));
            return true;
        }

        if ($this->isLocal) {
            $variable = $origin->getVariables()[$name] ?? null;
            if (!($variable instanceof ListVariable)) $variable = new ListVariable($name, []);
            $variable->addValue($value);
            $origin->addVariable($variable);
        } else {
            $variable = $helper->get($name);
            if (!($variable instanceof ListVariable)) $variable = new ListVariable($name, []);
            $variable->addValue($value);
            $helper->add($variable);
        }
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []) {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@action.variable.form.name", Language::get("form.example", ["aieuo"]), $default[1] ?? $this->getVariableName()),
                new Input("@action.variable.form.value", Language::get("form.example", ["aeiuo"]), $default[2] ?? $this->getVariableValue()),
                new Toggle("@action.variable.form.global", $default[3] ?? !$this->isLocal),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $errors = [];
        $name = $data[1];
        $value = $data[2];
        if ($name === "") {
            $errors[] = ["@form.insufficient", 1];
        }
        return ["status" => empty($errors), "contents" => [$name, $value, !$data[3]], "cancel" => $data[4], "errors" => $errors];
    }

    public function parseFromSaveData(array $content): ?Process {
        if (!isset($content[2])) return null;
        $this->setVariableName($content[0]);
        $this->setVariableValue($content[1]);
        $this->isLocal = $content[2];
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getVariableName(), $this->getVariableValue(), $this->isLocal];
    }
}