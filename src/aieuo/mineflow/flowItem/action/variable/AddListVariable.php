<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\variable;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\Main;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\Variable;

class AddListVariable extends FlowItem {

    protected string $id = self::ADD_LIST_VARIABLE;

    protected string $name = "action.addListVariable.name";
    protected string $detail = "action.addListVariable.detail";
    protected array $detailDefaultReplace = ["name", "scope", "value"];

    protected string $category = FlowItemCategory::VARIABLE;

    private string $variableName;
    /** @var string[] */
    private array $variableValue;
    private bool $isLocal;

    public function __construct(string $value = "", string $name = "", bool $local = true) {
        $this->variableName = $name;
        $this->variableValue = array_map("trim", explode(",", $value));
        $this->isLocal = $local;
    }

    public function setVariableName(string $variableName): void {
        $this->variableName = $variableName;
    }

    public function getVariableName(): string {
        return $this->variableName;
    }

    public function setVariableValue(array $variableValue): void {
        $this->variableValue = $variableValue;
    }

    public function getVariableValue(): array {
        return $this->variableValue;
    }

    public function isDataValid(): bool {
        return $this->variableName !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getVariableName(), $this->isLocal ? "local" : "global", implode(",", $this->getVariableValue())]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $helper = Mineflow::getVariableHelper();
        $name = $source->replaceVariables($this->getVariableName());
        $values = $this->getVariableValue();

        $variable = $this->isLocal ? $source->getVariable($name) : $helper->get($name);
        if ($variable === null) {
            throw new InvalidFlowValueException($this->getName(), Language::get("variable.notFound", [$name]));
        }
        if (!($variable instanceof ListVariable)) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.addListVariable.error.existsOtherType", [$name, (string)$variable]));
        }

        foreach ($values as $value) {
            $addVariable = $helper->copyOrCreateVariable($value, $source);
            $variable->appendValue($addVariable);
        }
        yield true;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ExampleInput("@action.variable.form.name", "aieuo", $this->getVariableName(), true),
            new ExampleInput("@action.variable.form.value", "aiueo", implode(",", $this->getVariableValue()), false),
            new Toggle("@action.variable.form.global", !$this->isLocal),
        ];
    }

    public function parseFromFormData(array $data): array {
        return [$data[0], array_map("trim", explode(",", $data[1])), !$data[2]];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setVariableName($content[0]);
        $this->setVariableValue($content[1]);
        $this->isLocal = $content[2];
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getVariableName(), $this->getVariableValue(), $this->isLocal];
    }

    public function getAddingVariables(): array {
        return [
            $this->getVariableName() => new DummyVariable(DummyVariable::LIST, "[".implode(",", $this->getVariableValue())."]")
        ];
    }
}
