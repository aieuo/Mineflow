<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\variable\Variable;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Categories;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\Main;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\variable\ListVariable;

class AddListVariable extends Action {

    protected $id = self::ADD_LIST_VARIABLE;

    protected $name = "action.addListVariable.name";
    protected $detail = "action.addListVariable.detail";
    protected $detailDefaultReplace = ["name", "scope", "value"];

    protected $category = Categories::CATEGORY_ACTION_VARIABLE;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;

    /** @var string */
    private $variableName;
    /** @var string */
    private $variableValue;
    /** @var bool */
    private $isLocal = true;

    public function __construct(string $value = "", string $name = "", bool $local = true) {
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
        return $this->variableName !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getVariableName(), $this->isLocal ? "local" : "global", $this->getVariableValue()]);
    }

    public function execute(Recipe $origin): bool {
        $this->throwIfCannotExecute();

        $helper = Main::getVariableHelper();
        $name = $origin->replaceVariables($this->getVariableName());
        $value = $origin->replaceVariables($this->getVariableValue());

        if ($value === "") {
            if ($this->isLocal) $origin->addVariable(new ListVariable([], $name));
            else $helper->add(new ListVariable([], $name));
            return true;
        }

        $type = $helper->getType($value);
        $addVariable = Variable::create($helper->currentType($value), "", $type);
        if ($this->isLocal) {
            $variable = $origin->getVariables()[$name] ?? new ListVariable([], $name);
            if (!($variable instanceof ListVariable)) return false;
            $variable->addValue($addVariable);
            $origin->addVariable($variable);
        } else {
            $variable = $helper->get($name) ?? new ListVariable([], $name);
            if (!($variable instanceof ListVariable)) return false;
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
                new Input("@action.variable.form.value", Language::get("form.example", ["aiueo"]), $default[2] ?? $this->getVariableValue()),
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

    public function loadSaveData(array $content): Action {
        if (!isset($content[2])) throw new \OutOfBoundsException();
        $this->setVariableName($content[0]);
        $this->setVariableValue($content[1]);
        $this->isLocal = $content[2];
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getVariableName(), $this->getVariableValue(), $this->isLocal];
    }
}