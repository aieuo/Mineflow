<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\variable;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\NumberVariable;
use SOFe\AwaitGenerator\Await;

class CountListVariable extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    protected string $returnValueType = self::RETURN_VARIABLE_VALUE;

    private StringArgument $variableName;
    private StringArgument $resultName;

    public function __construct(string $variableName = "", string $resultName = "count") {
        parent::__construct(self::COUNT_LIST_VARIABLE, FlowItemCategory::VARIABLE);

        $this->variableName = new StringArgument("name", $variableName, example: "list");
        $this->resultName = new StringArgument("result", $resultName, "@action.form.resultVariableName", example: "result");
    }

    public function getDetailDefaultReplaces(): array {
        return ["name", "result"];
    }

    public function getDetailReplaces(): array {
        return [$this->variableName->get(), $this->resultName->get()];
    }

    public function getVariableName(): StringArgument {
        return $this->variableName;
    }

    public function getResultName(): StringArgument {
        return $this->resultName;
    }

    public function isDataValid(): bool {
        return $this->variableName->isNotEmpty() and !empty($this->resultName->get());
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $name = $this->variableName->getString($source);
        $resultName = $this->resultName->getString($source);

        $variable = $source->getVariable($name) ?? Mineflow::getVariableHelper()->getNested($name);

        if (!($variable instanceof ListVariable)) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.count.error.notList"));
        }

        $count = count($variable->getValue());
        $source->addVariable($resultName, new NumberVariable($count));

        yield Await::ALL;
        return $count;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->variableName->createFormElement($variables),
            $this->resultName->createFormElement($variables),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->variableName->set($content[0]);
        $this->resultName->set($content[1]);
    }

    public function serializeContents(): array {
        return [$this->variableName->get(), $this->resultName->get()];
    }

    public function getAddingVariables(): array {
        return [
            $this->resultName->get() => new DummyVariable(NumberVariable::class)
        ];
    }
}
