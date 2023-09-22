<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\variable;

use aieuo\mineflow\flowItem\base\ConditionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\condition\Condition;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\EditFormResponseProcessor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\registry\VariableRegistry;
use SOFe\AwaitGenerator\Await;

class ExistsListVariableKey extends FlowItem implements Condition {
    use ConditionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    public function __construct(
        private string $variableName = "",
        private string $variableKey = "",
        private bool   $isLocal = true
    ) {
        parent::__construct(self::EXISTS_LIST_VARIABLE_KEY, FlowItemCategory::VARIABLE);
    }

    public function getDetailDefaultReplaces(): array {
        return ["scope", "name", "key"];
    }

    public function getDetailReplaces(): array {
        return [$this->isLocal ? "local" : "global", $this->getVariableName(), $this->getKey()];
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

    public function isDataValid(): bool {
        return $this->variableName !== "" and $this->variableKey !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $helper = Mineflow::getVariableHelper();
        $name = $source->replaceVariables($this->getVariableName());
        $key = $source->replaceVariables($this->getKey());

        $variable = $this->isLocal ? $source->getVariable($name) : VariableRegistry::global()->get($name);
        if (!($variable instanceof ListVariable)) return false;
        $value = $variable->getValue();

        yield Await::ALL;
        return isset($value[$key]);
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            new ExampleInput("@action.variable.form.name", "aieuo", $this->getVariableName(), true),
            new ExampleInput("@action.variable.form.key", "auieo", $this->getKey(), true),
            new Toggle("@action.variable.form.global", !$this->isLocal),
        ])->response(function (EditFormResponseProcessor $response) {
            $response->logicalNOT(2);
        });
    }

    public function loadSaveData(array $content): void {
        $this->setVariableName($content[0]);
        $this->setKey($content[1]);
        $this->isLocal = $content[2];
    }

    public function serializeContents(): array {
        return [$this->getVariableName(), $this->getKey(), $this->isLocal];
    }
}
