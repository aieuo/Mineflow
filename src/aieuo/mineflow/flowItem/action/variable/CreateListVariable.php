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

    public function __construct(string $variableName = "", string $value = "", bool $isLocal = true) {
        parent::__construct(self::CREATE_LIST_VARIABLE, FlowItemCategory::VARIABLE);

        $this->setArguments([
            new StringArgument("name", $variableName, "@action.variable.form.name", example: "aieuo"),
            new StringArrayArgument("value", $value, "@action.variable.form.value", example: "aiueo", optional: true),
            new IsLocalVariableArgument("scope", $isLocal),
        ]);
    }

    public function getVariableName(): StringArgument {
        return $this->getArguments()[0];
    }

    public function getVariableValue(): StringArrayArgument {
        return $this->getArguments()[1];
    }

    public function getIsLocal(): IsLocalVariableArgument {
        return $this->getArguments()[2];
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $helper = Mineflow::getVariableHelper();
        $name = $this->getVariableName()->getString($source);
        $values = $this->getVariableValue()->getRawArray();

        $variable = new ListVariable([]);

        foreach ($values as $value) {
            if ($value === "") continue;

            $addVariable = $helper->copyOrCreateVariable($value, $source);
            $variable->appendValue($addVariable);
        }

        if ($this->getIsLocal()->getBool()) {
            $source->addVariable($name, $variable);
        } else {
            $helper->add($name, $variable);
        }

        yield Await::ALL;
    }

    public function getAddingVariables(): array {
        return [
            $this->getVariableName()->getRawString() => new DummyVariable(ListVariable::class)
        ];
    }
}
