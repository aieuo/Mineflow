<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\variable;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\EditFormResponseProcessor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\MapVariable;
use SOFe\AwaitGenerator\Await;

class AddMapVariable extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private StringArgument $variableName;
    private StringArgument $variableKey;
    private StringArgument $variableValue;

    public function __construct(string $variableName = "", string $variableKey = "", string $variableValue = "", private bool $isLocal = true) {
        parent::__construct(self::ADD_MAP_VARIABLE, FlowItemCategory::VARIABLE);

        $this->variableName = new StringArgument("name", $variableName, "@action.variable.form.name", example: "aieuo");
        $this->variableKey = new StringArgument("key", $variableKey, "@action.variable.form.key", example: "auieo");
        $this->variableValue = new StringArgument("value", $variableValue, "@action.variable.form.value", example: "aeiuo", optional: true);
    }

    public function getDetailDefaultReplaces(): array {
        return ["name", "scope", "key", "value"];
    }

    public function getDetailReplaces(): array {
        return [$this->variableName->get(), $this->isLocal ? "local" : "global", $this->variableKey->get(), $this->variableValue->get()];
    }

    public function getVariableName(): StringArgument {
        return $this->variableName;
    }

    public function getKey(): StringArgument {
        return $this->variableKey;
    }

    public function getVariableValue(): StringArgument {
        return $this->variableValue;
    }

    public function isDataValid(): bool {
        return $this->variableName->isNotEmpty() and $this->variableKey->isNotEmpty();
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $helper = Mineflow::getVariableHelper();
        $name = $this->variableName->getString($source);
        $key = $this->variableKey->getString($source);

        $value = $this->variableValue->get();
        $addVariable = $helper->copyOrCreateVariable($value, $source);
        $variable = $this->isLocal ? $source->getVariable($name) : $helper->get($name);
        if ($variable === null) {
            throw new InvalidFlowValueException($this->getName(), Language::get("variable.notFound", [$name]));
        }
        if (!($variable instanceof MapVariable)) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.addListVariable.error.existsOtherType", [$name, (string)$variable]));
        }
        $variable->setValueAt($key, $addVariable);

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->variableName->createFormElement($variables),
            $this->variableKey->createFormElement($variables),
            $this->variableValue->createFormElement($variables),
            new Toggle("@action.variable.form.global", !$this->isLocal),
        ])->response(function (EditFormResponseProcessor $response) {
            $response->logicalNOT(3);
        });
    }

    public function loadSaveData(array $content): void {
        $this->variableName->set($content[0]);
        $this->variableKey->set($content[1]);
        $this->variableValue->set($content[2]);
        $this->isLocal = $content[3];
    }

    public function serializeContents(): array {
        return [$this->variableName->get(), $this->variableKey->get(), $this->variableValue->get(), $this->isLocal];
    }

    public function getAddingVariables(): array {
        return [
            $this->variableName->get() => new DummyVariable(MapVariable::class)
        ];
    }
}
