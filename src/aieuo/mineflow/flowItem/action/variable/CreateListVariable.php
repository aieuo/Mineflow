<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\variable;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\Main;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\ListVariable;
use SOFe\AwaitGenerator\Await;
use function implode;

class CreateListVariable extends FlowItem {
    use ActionNameWithMineflowLanguage;

    /** @var string[] */
    private array $variableValue;

    public function __construct(
        string         $value = "",
        private string $variableName = "",
        private bool   $isLocal = true
    ) {
        parent::__construct(self::CREATE_LIST_VARIABLE, FlowItemCategory::VARIABLE);

        $this->variableValue = array_map("trim", explode(",", $value));
    }

    public function getDetailDefaultReplaces(): array {
        return ["name", "scope", "value"];
    }

    public function getDetailReplaces(): array {
        return [$this->getVariableName(), $this->isLocal ? "local" : "global", implode(",", $this->getVariableValue())];
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

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $helper = Mineflow::getVariableHelper();
        $name = $source->replaceVariables($this->getVariableName());
        $values = $this->getVariableValue();

        $variable = new ListVariable([]);

        foreach ($values as $value) {
            if ($value === "") continue;

            $addVariable = $helper->copyOrCreateVariable($value, $source);
            $variable->appendValue($addVariable);
        }

        if ($this->isLocal) {
            $source->addVariable($name, $variable);
        } else {
            $helper->add($name, $variable);
        }

        yield Await::ALL;
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
            $this->getVariableName() => new DummyVariable(ListVariable::class)
        ];
    }
}
