<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\variable;

use aieuo\mineflow\flowItem\argument\IsLocalVariableArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\argument\StringArrayArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\ListVariable;
use SOFe\AwaitGenerator\Await;

class CreateListVariable extends SimpleAction {

    private StringArgument $variableName;
    private StringArrayArgument $variableValue;
    private IsLocalVariableArgument $isLocal;

    public function __construct(string $variableName = "", string $value = "", bool $isLocal = true) {
        parent::__construct(self::CREATE_LIST_VARIABLE, FlowItemCategory::VARIABLE);

        $this->setArguments([
            $this->variableName = new StringArgument("name", $variableName, "@action.variable.form.name", example: "aieuo"),
            $this->variableValue = new StringArrayArgument("value", $value, "@action.variable.form.value", example: "aiueo", optional: true),
            $this->isLocal = new IsLocalVariableArgument("scope", $isLocal),
        ]);
    }

    public function getVariableName(): StringArgument {
        return $this->variableName;
    }

    public function getVariableValue(): StringArrayArgument {
        return $this->variableValue;
    }

    public function getIsLocal(): IsLocalVariableArgument {
        return $this->isLocal;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $helper = Mineflow::getVariableHelper();
        $name = $this->variableName->getString($source);
        $values = $this->variableValue->getRawArray();

        $variable = new ListVariable([]);

        foreach ($values as $value) {
            if ($value === "") continue;

            $addVariable = $helper->copyOrCreateVariable($value, $source);
            $variable->appendValue($addVariable);
        }

        if ($this->isLocal->getBool()) {
            $source->addVariable($name, $variable);
        } else {
            $helper->add($name, $variable);
        }

        yield Await::ALL;
    }

    public function getAddingVariables(): array {
        return [
            $this->variableName->getRawString() => new DummyVariable(ListVariable::class)
        ];
    }
}
