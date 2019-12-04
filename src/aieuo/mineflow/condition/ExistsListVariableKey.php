<?php

namespace aieuo\mineflow\condition;

use pocketmine\entity\Entity;
use aieuo\mineflow\variable\Variable;
use aieuo\mineflow\utils\Logger;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Categories;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\Main;
use aieuo\mineflow\formAPI\element\Toggle;

class ExistsListVariableKey extends Condition {

    protected $id = self::EXISTS_LIST_VARIABLE_KEY;

    protected $name = "@condition.existsListVariableKey.name";
    protected $description = "@condition.existsListVariableKey.description";
    protected $detail = "condition.existsListVariableKey.detail";

    protected $category = Categories::CATEGORY_CONDITION_VARIABLE;

    /** @var string */
    private $variableName;
    /** @var string */
    private $variableKey;
    /** @var bool */
    private $isLocal = true;

    public function __construct(string $name = "", string $key = "", bool $local = true) {
        $this->variableName = $name;
        $this->variableKey = $key;
        $this->isLocal = $local;
    }

    public function setVariableName(string $variableName) {
        $this->variableName = $variableName;
    }

    public function getVariableName(): string {
        return $this->variableName;
    }

    public function setKey(string $variableKey) {
        $this->variableKey = $variableKey;
    }

    public function getKey(): string {
        return $this->variableKey;
    }

    public function isDataValid(): bool {
        return !empty($this->variableName) and !empty($this->variableKey);
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->isLocal ? "local" : "global", $this->getVariableName(), $this->getKey()]);
    }

    public function execute(?Entity $target, ?Recipe $origin = null): ?bool {
        if (!$this->isDataValid()) {
            Logger::warning(Language::get("invalid.contents", [$this->getName()]), $target);
            return null;
        }

        $helper = Main::getInstance()->getVariableHelper();
        $name = $this->getVariableName();
        $key = $this->getKey();
        $isRecipe = $origin instanceof Recipe;

        if ($isRecipe) {
            $name = $origin->replaceVariables($name);
            $key = $origin->replaceVariables($key);
        } elseif (!$this->isLocal) {
            Logger::warning(Language::get("condition.error", [Language::get("action.error.recipe"), $this->getName()]), $target);
            return null;
        }

        $variable = $this->isLocal ? $origin->getVariable($name) : $helper->get($name);
        if (!($variable instanceof Variable)) return false;
        $value = $variable->getValue();
        return isset($value[$key]);
    }

    public function getEditForm(array $default = [], array $errors = []) {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@action.variable.form.name", Language::get("form.example", ["aieuo"]), $default[1] ?? $this->getVariableName()),
                new Input("@action.variable.form.key", Language::get("form.example", ["auieo"]), $default[2] ?? $this->getKey()),
                new Toggle("@action.variable.form.global", $default[4] ?? !$this->isLocal),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $errors = [];
        $name = $data[1];
        $key = $data[2];
        if ($name === "") {
            $errors[] = ["@form.insufficient", 1];
        }
        if ($key === "") {
            $errors[] = ["@form.insufficient", 2];
        }
        return ["status" => empty($errors), "contents" => [$name, $key, !$data[3]], "cancel" => $data[4], "errors" => $errors];
    }

    public function parseFromSaveData(array $content): ?Condition {
        if (!isset($content[2])) return null;
        $this->setVariableName($content[0]);
        $this->setKey($content[1]);
        $this->isLocal = $content[2];
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getVariableName(), $this->getKey(), $this->isLocal];
    }
}