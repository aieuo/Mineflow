<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\Main;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\MapVariable;
use aieuo\mineflow\variable\Variable;

class AddMapVariable extends FlowItem {

    protected $id = self::ADD_MAP_VARIABLE;

    protected $name = "action.addMapVariable.name";
    protected $detail = "action.addMapVariable.detail";
    protected $detailDefaultReplace = ["name", "scope", "key", "value"];

    protected $category = Category::VARIABLE;

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

    public function setVariableName(string $variableName): void {
        $this->variableName = $variableName;
    }

    public function getVariableName(): string {
        return $this->variableName;
    }

    public function setKey(string $variableKey): void {
        $this->variableKey = $variableKey;
    }

    public function getKey(): string {
        return $this->variableKey;
    }

    public function setVariableValue(string $variableValue): void {
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

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $helper = Main::getVariableHelper();
        $name = $source->replaceVariables($this->getVariableName());
        $key = $source->replaceVariables($this->getKey());
        $value = $source->replaceVariables($this->getVariableValue());

        $type = $helper->getType($value);

        $value = $this->getVariableValue();
        if ($helper->isVariableString($value)) {
            $addVariable = $source->getVariable(substr($value, 1, -1)) ?? $helper->get(substr($value, 1, -1));
            if ($addVariable === null) {
                $value = $helper->replaceVariables($value, $source->getVariables());
                $addVariable = Variable::create($helper->currentType($value), $key, $type);
            } else {
                $addVariable->setName($key);
            }
        } else {
            $value = $helper->replaceVariables($value, $source->getVariables());
            $addVariable = Variable::create($helper->currentType($value), $key, $type);
        }

        if ($this->isLocal) {
            $variable = $source->getVariable($name) ?? new MapVariable([], $name);
            if (!($variable instanceof MapVariable)) {
                throw new InvalidFlowValueException($this->getName(), Language::get("action.error", [
                    $this->getName(), ["action.addListVariable.error.existsOtherType", [$name, (string)$variable]]
                ]));
            }
            $variable->addValue($addVariable);
            $source->addVariable($variable);
        } else {
            $variable = $helper->get($name) ?? new MapVariable([], $name);
            if (!($variable instanceof MapVariable)) {
                throw new InvalidFlowValueException($this->getName(), Language::get("action.error", [
                    $this->getName(), ["action.addListVariable.error.existsOtherType", [$name, (string)$variable]]
                ]));
            }
            $variable->addValue($addVariable);
            $helper->add($variable);
        }
        yield true;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ExampleInput("@action.variable.form.name", "aieuo", $this->getVariableName(), true),
            new ExampleInput("@action.variable.form.key", "auieo", $this->getKey(), false),
            new ExampleInput("@action.variable.form.value", "aeiuo", $this->getVariableValue(), false),
            new Toggle("@action.variable.form.global", !$this->isLocal),
        ];
    }

    public function parseFromFormData(array $data): array {
        // TODO: AddListVariableのように区切って複数同時に追加できるようにする
        return ["contents" => [$data[0], $data[1], $data[2], !$data[3]]];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setVariableName($content[0]);
        $this->setKey($content[1]);
        $this->setVariableValue($content[2]);
        $this->isLocal = $content[3];
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getVariableName(), $this->getKey(), $this->getVariableValue(), $this->isLocal];
    }

    public function getAddingVariables(): array {
        return [new DummyVariable($this->getVariableName(), DummyVariable::MAP)];
    }
}