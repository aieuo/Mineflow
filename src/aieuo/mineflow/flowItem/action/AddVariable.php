<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\formAPI\Form;
use pocketmine\entity\Entity;
use aieuo\mineflow\variable\Variable;
use aieuo\mineflow\variable\StringVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\utils\Logger;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Categories;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\Main;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\formAPI\element\Dropdown;

class AddVariable extends Action {

    protected $id = self::ADD_VARIABLE;

    protected $name = "action.addVariable.name";
    protected $detail = "action.addVariable.detail";
    protected $detailDefaultReplace = ["name", "value", "type", "scope"];

    protected $category = Categories::CATEGORY_ACTION_VARIABLE;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;

    /** @var string */
    private $variableName;
    /** @var string */
    private $variableValue;
    /** @var int */
    private $variableType = Variable::STRING;
    /** @var bool */
    private $isLocal = true;

    /** @var array */
    private $variableTypes = ["string", "number"];

    public function __construct(string $name = "", string $value = "", int $type = Variable::STRING, bool $local = true) {
        $this->variableName = $name;
        $this->variableValue = $value;
        $this->variableType = $type;
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
        return !empty($this->variableName) and $this->variableValue !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getVariableName(), $this->getVariableValue(), $this->variableTypes[$this->variableType], $this->isLocal ? "local" : "global"]);
    }

    public function execute(?Entity $target, Recipe $origin): ?bool {
        if (!$this->canExecute($target)) return null;

        $name = $origin->replaceVariables($this->getVariableName());
        $value = $origin->replaceVariables($this->getVariableValue());

        switch ($this->variableType) {
            case Variable::STRING:
                $variable = new StringVariable($value, $name);
                break;
            case Variable::NUMBER:
                if (!$this->checkValidNumberDataAndAlert($value, null, null, $target)) return null;
                $variable = new NumberVariable((float)$value, $name);
                break;
            default:
                return false;
        }

        if ($this->isLocal) {
            $origin->addVariable($variable);
        } else {
            Main::getVariableHelper()->add($variable);
        }
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@action.variable.form.name", Language::get("form.example", ["aieuo"]), $default[1] ?? $this->getVariableName()),
                new Input("@action.variable.form.value", Language::get("form.example", ["aeiuo"]), $default[2] ?? $this->getVariableValue()),
                new Dropdown("@action.variable.form.type", $this->variableTypes, $default[3] ?? $this->variableType),
                new Toggle("@action.variable.form.global", $default[4] ?? !$this->isLocal),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $errors = [];
        $name = $data[1];
        $value = $data[2];
        $type = $data[3];
        if ($name === "") {
            $errors[] = ["@form.insufficient", 1];
        }
        $containsVariable = Main::getVariableHelper()->containsVariable($value);
        if ($value === "") {
            $errors[] = ["@form.insufficient", 1];
        } elseif ($type === Variable::NUMBER and !$containsVariable and !is_numeric($value)) {
            $errors[] = ["@mineflow.contents.notNumber", 1];
        }
        return ["status" => empty($errors), "contents" => [$name, $value, $type, !$data[4]], "cancel" => $data[5], "errors" => $errors];
    }

    public function loadSaveData(array $content): Action {
        if (!isset($content[3])) throw new \OutOfBoundsException();
        $this->setVariableName($content[0]);
        $this->setVariableValue($content[1]);
        $this->variableType = $content[2];
        $this->isLocal = $content[3];
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getVariableName(), $this->getVariableValue(), $this->variableType, $this->isLocal];
    }
}