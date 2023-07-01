<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\variable;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\argument\BooleanArgument;
use aieuo\mineflow\flowItem\argument\IsLocalVariableArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\MapVariable;
use SOFe\AwaitGenerator\Await;

class AddMapVariable extends SimpleAction {

    private StringArgument $variableName;
    private StringArgument $variableKey;
    private StringArgument $variableValue;
    private BooleanArgument $isLocal;

    public function __construct(string $variableName = "", string $variableKey = "", string $variableValue = "", bool $isLocal = true) {
        parent::__construct(self::ADD_MAP_VARIABLE, FlowItemCategory::VARIABLE);

        $this->setArguments([
            $this->variableName = new StringArgument("name", $variableName, "@action.variable.form.name", example: "aieuo"),
            $this->variableKey = new StringArgument("key", $variableKey, "@action.variable.form.key", example: "auieo"),
            $this->variableValue = new StringArgument("value", $variableValue, "@action.variable.form.value", example: "aeiuo", optional: true),
            $this->isLocal = new IsLocalVariableArgument("scope", $isLocal),
        ]);
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

    public function getIsLocal(): BooleanArgument {
        return $this->isLocal;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $helper = Mineflow::getVariableHelper();
        $name = $this->variableName->getString($source);
        $key = $this->variableKey->getString($source);

        $value = $this->variableValue->get();
        $addVariable = $helper->copyOrCreateVariable($value, $source);
        $variable = $this->isLocal->getBool() ? $source->getVariable($name) : $helper->get($name);
        if ($variable === null) {
            throw new InvalidFlowValueException($this->getName(), Language::get("variable.notFound", [$name]));
        }
        if (!($variable instanceof MapVariable)) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.addListVariable.error.existsOtherType", [$name, (string)$variable]));
        }
        $variable->setValueAt($key, $addVariable);

        yield Await::ALL;
    }

    public function getAddingVariables(): array {
        return [
            $this->variableName->get() => new DummyVariable(MapVariable::class)
        ];
    }
}
