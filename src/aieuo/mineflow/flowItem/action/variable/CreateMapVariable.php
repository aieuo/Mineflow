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
use aieuo\mineflow\variable\MapVariable;
use SOFe\AwaitGenerator\Await;

class CreateMapVariable extends SimpleAction {

    private StringArgument $variableName;
    private StringArrayArgument $variableKey;
    private StringArrayArgument $variableValue;
    private IsLocalVariableArgument $isLocal;

    public function __construct(string $variableName = "", string $key = "", string $value = "", bool $isLocal = true) {
        parent::__construct(self::CREATE_MAP_VARIABLE, FlowItemCategory::VARIABLE);

        $this->setArguments([
            $this->variableName = new StringArgument("name", $variableName, "@action.variable.form.name", example: "aieuo"),
            $this->variableKey = new StringArrayArgument("key", $key, "@action.variable.form.key", example: "aieuo"),
            $this->variableValue = new StringArrayArgument("value", $value, "@action.variable.form.value", example: "aieuo"),
            $this->isLocal = new IsLocalVariableArgument("scope", $isLocal),
        ]);
    }

    public function getVariableName(): StringArgument {
        return $this->variableName;
    }

    public function getVariableKey(): StringArrayArgument {
        return $this->variableKey;
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
        $keys = $this->variableKey->getArray($source);
        $values = $this->variableValue->getRawArray();

        $variable = new MapVariable([]);
        for ($i = 0, $iMax = count($keys); $i < $iMax; $i++) {
            $key = $keys[$i];
            $value = $values[$i] ?? "";
            if ($key === "") continue;

            $addVariable = $helper->copyOrCreateVariable($value, $source);
            $variable->setValueAt($key, $addVariable);
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
            $this->variableName->get() => new DummyVariable(MapVariable::class)
        ];
    }
}
