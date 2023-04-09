<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\variable;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\EditFormResponseProcessor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\MapVariable;
use SOFe\AwaitGenerator\Await;
use function array_map;
use function explode;
use function implode;

class CreateMapVariable extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

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

    public function getDetailDefaultReplaces(): array {
        return ["name", "scope", "key", "value"];
    }

    public function getDetailReplaces(): array {
        return [$this->getVariableName(), $this->isLocal ? "local" : "global", implode(",", $this->getKey()), implode(",", $this->getVariableValue())];
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

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $helper = Mineflow::getVariableHelper();
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

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            new ExampleInput("@action.variable.form.name", "aieuo", $this->getVariableName(), true),
            new ExampleInput("@action.variable.form.key", "auieo", implode(",", $this->getKey()), false),
            new ExampleInput("@action.variable.form.value", "aeiuo", implode(",", $this->getVariableValue()), false),
            new Toggle("@action.variable.form.global", !$this->isLocal),
        ])->response(function (EditFormResponseProcessor $response) {
            $response->preprocessAt(1, fn($value) => array_map("trim", explode(",", $value)));
            $response->preprocessAt(2, fn($value) => array_map("trim", explode(",", $value)));
            $response->logicalNOT(3);
        });
    }

    public function loadSaveData(array $content): void {
        $this->setVariableName($content[0]);
        $this->setKey($content[1]);
        $this->setVariableValue($content[2]);
        $this->isLocal = $content[3];
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
