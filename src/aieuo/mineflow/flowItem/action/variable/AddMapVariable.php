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
use aieuo\mineflow\variable\registry\VariableRegistry;
use aieuo\mineflow\libs\_30a18b127a564f2c\SOFe\AwaitGenerator\Await;

class AddMapVariable extends SimpleAction {

    public function __construct(string $variableName = "", string $variableKey = "", string $variableValue = "", bool $isLocal = true) {
        parent::__construct(self::ADD_MAP_VARIABLE, FlowItemCategory::VARIABLE);

        $this->setArguments([
            StringArgument::create("name", $variableName, "@action.variable.form.name")->example("aieuo"),
            StringArgument::create("key", $variableKey, "@action.variable.form.key")->example("auieo"),
            StringArgument::create("value", $variableValue, "@action.variable.form.value")->optional()->example("aeiuo"),
            IsLocalVariableArgument::create("scope", $isLocal),
        ]);
    }

    public function getVariableName(): StringArgument {
        return $this->getArgument("name");
    }

    public function getVariableKey(): StringArgument {
        return $this->getArgument("key");
    }

    public function getVariableValue(): StringArgument {
        return $this->getArgument("value");
    }

    public function getIsLocal(): BooleanArgument {
        return $this->getArgument("scope");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $helper = Mineflow::getVariableHelper();
        $name = $this->getVariableName()->getString($source);
        $key = $this->getVariableKey()->getString($source);

        $value = $this->getVariableValue()->getRawString();
        $addVariable = $helper->copyOrCreateVariable($value, $source->getVariableRegistryCopy());
        $variable = $this->getIsLocal()->getBool() ? $source->getVariable($name) : VariableRegistry::global()->get($name);
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
            (string)$this->getVariableName() => new DummyVariable(MapVariable::class)
        ];
    }
}