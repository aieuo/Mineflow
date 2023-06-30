<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\variable;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\NumberVariable;
use SOFe\AwaitGenerator\Await;

class CountListVariable extends SimpleAction {

    protected string $returnValueType = self::RETURN_VARIABLE_VALUE;

    private StringArgument $variableName;
    private StringArgument $resultName;

    public function __construct(string $variableName = "", string $resultName = "count") {
        parent::__construct(self::COUNT_LIST_VARIABLE, FlowItemCategory::VARIABLE);

        $this->setArguments([
            $this->variableName = new StringArgument("name", $variableName, example: "list"),
            $this->resultName = new StringArgument("result", $resultName, "@action.form.resultVariableName", example: "result"),
        ]);
    }

    public function getVariableName(): StringArgument {
        return $this->variableName;
    }

    public function getResultName(): StringArgument {
        return $this->resultName;
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

    public function getAddingVariables(): array {
        return [
            $this->resultName->get() => new DummyVariable(NumberVariable::class)
        ];
    }
}
