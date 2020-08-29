<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\ExampleInput;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\variable\Variable;
use aieuo\mineflow\variable\MapVariable;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\Main;
use aieuo\mineflow\formAPI\element\Toggle;

class AddMapVariable extends Action {

    protected $id = self::ADD_MAP_VARIABLE;

    protected $name = "action.addMapVariable.name";
    protected $detail = "action.addMapVariable.detail";
    protected $detailDefaultReplace = ["name", "scope", "key", "value"];

    protected $category = Category::VARIABLE;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;

    /** @var string */
    private $variableName;
    /** @var string */
    private $variableKey;
    /** @var string */
    private $variableValue;
    /** @var bool */
    private $isLocal;

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
        return $this->variableName !== "" and $this->variableKey !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getVariableName(), $this->isLocal ? "local" : "global", $this->getKey(), $this->getVariableValue()]);
    }

    public function execute(Recipe $origin): bool {
        $this->throwIfCannotExecute();

        $helper = Main::getVariableHelper();
        $name = $origin->replaceVariables($this->getVariableName());
        $key = $origin->replaceVariables($this->getKey());
        $value = $origin->replaceVariables($this->getVariableValue());

        $type = $helper->getType($value);

        $value = $this->getVariableValue();
        if (!$helper->isVariableString($value)) {
            $value = $helper->replaceVariables($value, $origin->getVariables());
            $addVariable = Variable::create($helper->currentType($value), $key, $type);
        } else {
            $addVariable = $origin->getVariable(substr($value, 1, -1)) ?? $helper->get(substr($value, 1, -1));
            if ($addVariable === null) {
                $value = $helper->replaceVariables($value, $origin->getVariables());
                $addVariable = Variable::create($helper->currentType($value), $key, $type);
            } else {
                $addVariable->setName($key);
            }
        }

        if ($this->isLocal) {
            $variable = $origin->getVariable($name) ?? new MapVariable([], $name);
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
                new ExampleInput("@action.variable.form.name", "aieuo", $default[1] ?? $this->getVariableName(), true),
                new ExampleInput("@action.variable.form.key", "auieo", $default[2] ?? $this->getKey(), false),
                new ExampleInput("@action.variable.form.value", "aeiuo", $default[3] ?? $this->getVariableValue(), false),
                new Toggle("@action.variable.form.global", $default[4] ?? !$this->isLocal),
                new CancelToggle()
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        // TODO: AddListVariableのように区切って複数同時に追加できるようにする
        return ["contents" => [$data[1], $data[2], $data[3], !$data[4]], "cancel" => $data[5], "errors" => []];
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