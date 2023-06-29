<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\variable;

use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\ConditionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\condition\Condition;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\EditFormResponseProcessor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\variable\ListVariable;
use SOFe\AwaitGenerator\Await;

class ExistsListVariableKey extends FlowItem implements Condition {
    use ConditionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private StringArgument $variableName;
    private StringArgument $variableKey;

    public function __construct(
        string $variableName = "",
        string $variableKey = "",
        private bool   $isLocal = true
    ) {
        parent::__construct(self::EXISTS_LIST_VARIABLE_KEY, FlowItemCategory::VARIABLE);

        $this->variableName = new StringArgument("name", $variableName, "@action.variable.form.name", example: "aieuo");
        $this->variableKey = new StringArgument("key", $variableKey, "@action.variable.form.key", example: "auieo");
    }

    public function getDetailDefaultReplaces(): array {
        return ["scope", "name", "key"];
    }

    public function getDetailReplaces(): array {
        return [$this->isLocal ? "local" : "global", $this->variableName->get(), $this->variableKey->get()];
    }

    public function getVariableName(): StringArgument {
        return $this->variableName;
    }

    public function getKey(): StringArgument {
        return $this->variableKey;
    }

    public function isDataValid(): bool {
        return $this->variableName->isNotEmpty() and $this->variableKey->isNotEmpty();
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $helper = Mineflow::getVariableHelper();
        $name = $this->variableName->getString($source);
        $key = $this->variableKey->getString($source);

        $variable = $this->isLocal ? $source->getVariable($name) : $helper->get($name);
        if (!($variable instanceof ListVariable)) return false;
        $value = $variable->getValue();

        yield Await::ALL;
        return isset($value[$key]);
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->variableName->createFormElement($variables),
            $this->variableKey->createFormElement($variables),
            new Toggle("@action.variable.form.global", !$this->isLocal),
        ])->response(function (EditFormResponseProcessor $response) {
            $response->logicalNOT(2);
        });
    }

    public function loadSaveData(array $content): void {
        $this->variableName->set($content[0]);
        $this->variableKey->set($content[1]);
        $this->isLocal = $content[2];
    }

    public function serializeContents(): array {
        return [$this->variableName->get(), $this->variableKey->get(), $this->isLocal];
    }
}
