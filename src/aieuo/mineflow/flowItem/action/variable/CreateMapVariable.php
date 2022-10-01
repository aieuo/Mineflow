<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\variable;

use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\Main;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\MapVariable;

class CreateMapVariable extends FlowItem {

    protected string $name = "action.createMap.name";
    protected string $detail = "action.createMap.detail";
    protected array $detailDefaultReplace = ["name", "scope", "key", "value"];

    private array $variableKey;
    private array $variableValue;

    public function __construct(
        private string $variableName = "",
        string         $key = "",
        string         $value = "",
        private bool   $isLocal = true
    ) {
        parent::__construct(self::CREATE_MAP_VARIABLE, FlowItemCategory::VARIABLE);

        $this->variableKey = array_map("trim", explode(",", $key));
        $this->variableValue = array_map("trim", explode(",", $value));
    }

    public function setVariableName(string $variableName): void {
        $this->variableName = $variableName;
    }

    public function getVariableName(): string {
        return $this->variableName;
    }

    public function setKey(array $variableKey): void {
        $this->variableKey = $variableKey;
    }

    public function getKey(): array {
        return $this->variableKey;
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
        return Language::get($this->detail, [$this->getVariableName(), $this->isLocal ? "local" : "global", implode(",", $this->getKey()), implode(",", $this->getVariableValue())]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $helper = Main::getVariableHelper();
        $name = $source->replaceVariables($this->getVariableName());
        $keys = array_map(fn(string $key) => $source->replaceVariables($key), $this->getKey());
        $values = $this->getVariableValue();

        $variable = new MapVariable([]);
        for ($i = 0, $iMax = count($keys); $i < $iMax; $i++) {
            $key = $keys[$i];
            $value = $values[$i] ?? "";
            if ($key === "") continue;

            $addVariable = $helper->copyOrCreateVariable($value, $source);
            $variable->setValueAt($key, $addVariable);
        }

        if ($this->isLocal) {
            $source->addVariable($name, $variable);
        } else {
            $helper->add($name, $variable);
        }
        yield true;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ExampleInput("@action.variable.form.name", "aieuo", $this->getVariableName(), true),
            new ExampleInput("@action.variable.form.key", "auieo", implode(",", $this->getKey()), false),
            new ExampleInput("@action.variable.form.value", "aeiuo", implode(",", $this->getVariableValue()), false),
            new Toggle("@action.variable.form.global", !$this->isLocal),
        ];
    }

    public function parseFromFormData(array $data): array {
        $name = $data[0];
        $key = array_map("trim", explode(",", $data[1]));
        $value = array_map("trim", explode(",", $data[2]));
        return [$name, $key, $value, !$data[3]];
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
        return [
            $this->getVariableName() => new DummyVariable(MapVariable::class)
        ];
    }
}
