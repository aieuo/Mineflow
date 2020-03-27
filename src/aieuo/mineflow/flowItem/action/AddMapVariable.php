<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\variable\Variable;
use pocketmine\entity\Entity;
use aieuo\mineflow\variable\MapVariable;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Categories;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\Main;
use aieuo\mineflow\formAPI\element\Toggle;

class AddMapVariable extends Action {

    protected $id = self::ADD_MAP_VARIABLE;

    protected $name = "action.addMapVariable.name";
    protected $detail = "action.addMapVariable.detail";
    protected $detailDefaultReplace = ["name", "scope", "key", "value"];

    protected $category = Categories::CATEGORY_ACTION_VARIABLE;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;
    protected $returnValueType = self::RETURN_NONE;

    /** @var string */
    private $variableName;
    /** @var string */
    private $variableKey;
    /** @var string */
    private $variableValue;
    /** @var bool */
    private $isLocal = true;

    public function __construct(string $name = "", string $key = "", string $value = "", bool $local = true) {
        $this->variableName = $name;
        $this->variableKey = $key;
        $this->variableValue = $value;
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

    public function setVariableValue(string $variableValue) {
        $this->variableValue = $variableValue;
    }

    public function getVariableValue(): string {
        return $this->variableValue;
    }

    public function isDataValid(): bool {
        return !empty($this->variableName) and ((!empty($this->variableKey) and !empty($this->variableValue)) or (empty($this->variableKey) and empty($this->variableValue)));
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getVariableName(), $this->isLocal ? "local" : "global", $this->getKey(), $this->getVariableValue()]);
    }

    public function execute(?Entity $target, Recipe $origin): bool {
        $this->throwIfCannotExecute($target);

        $helper = Main::getVariableHelper();
        $name = $origin->replaceVariables($this->getVariableName());
        $key = $origin->replaceVariables($this->getKey());
        $value = $origin->replaceVariables($this->getVariableValue());

        if ($key === "" and $value === "") {
            if ($this->isLocal) $origin->addVariable(new MapVariable([], $name));
            else $helper->add(new MapVariable([], $name));
            return true;
        }

        $type = $helper->getType($value);
        $addVariable = Variable::create($helper->currentType($value), "", $type);
        if ($this->isLocal) {
            $variable = $origin->getVariables()[$name] ?? new MapVariable([], $name);
            if (!($variable instanceof MapVariable)) return false;
            $variable->addValue($addVariable);
            $origin->addVariable($variable);
        } else {
            $variable = $helper->get($name) ?? new MapVariable([], $name);
            if (!($variable instanceof MapVariable)) return false;
            $variable->addValue($addVariable);
            $helper->add($variable);
        }
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@action.variable.form.name", Language::get("form.example", ["aieuo"]), $default[1] ?? $this->getVariableName()),
                new Input("@action.variable.form.key", Language::get("form.example", ["auieo"]), $default[2] ?? $this->getKey()),
                new Input("@action.variable.form.value", Language::get("form.example", ["aeiuo"]), $default[3] ?? $this->getVariableValue()),
                new Toggle("@action.variable.form.global", $default[4] ?? !$this->isLocal),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $errors = [];
        $name = $data[1];
        $key = $data[2];
        $value = $data[3];
        if ($name === "") {
            $errors[] = ["@form.insufficient", 1];
        }
        if ($key === "" and $value !== "") {
            $errors[] = ["@form.insufficient", 2];
        }
        if ($key !== "" and $value === "") {
            $errors[] = ["@form.insufficient", 3];
        }
        return ["status" => empty($errors), "contents" => [$name, $key, $value, !$data[4]], "cancel" => $data[5], "errors" => $errors];
    }

    public function loadSaveData(array $content): Action {
        if (!isset($content[3])) throw new \OutOfBoundsException();
        $this->setVariableName($content[0]);
        $this->setKey($content[1]);
        $this->setVariableValue($content[2]);
        $this->isLocal = $content[3];
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getVariableName(), $this->getKey(), $this->getVariableValue(), $this->isLocal];
    }
}