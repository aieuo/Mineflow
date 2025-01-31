<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\variable;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\argument\BooleanArgument;
use aieuo\mineflow\flowItem\argument\IsLocalVariableArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\argument\StringArrayArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\registry\VariableRegistry;
use aieuo\mineflow\libs\_6b4cfdc0a11de6c9\SOFe\AwaitGenerator\Await;

class AddListVariable extends SimpleAction {

    public function __construct(
        string $variableName = "",
        string $value = "",
        bool   $isLocal = true
    ) {
        parent::__construct(self::ADD_LIST_VARIABLE, FlowItemCategory::VARIABLE);

        $this->setArguments([
            StringArgument::create("name", $variableName, "@action.variable.form.name")->example("aieuo"),
            StringArrayArgument::create("value", $value, "@action.variable.form.value")->example("aiueo"),
            IsLocalVariableArgument::create("scope", $isLocal),
        ]);
    }

    public function getVariableName(): StringArgument {
        return $this->getArgument("name");
    }

    public function getValue(): StringArrayArgument {
        return $this->getArgument("value");
    }

    public function getIsLocal(): BooleanArgument {
        return $this->getArgument("scope");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $helper = Mineflow::getVariableHelper();
        $name = $this->getVariableName()->getString($source);
        $values = $this->getValue()->getRawArray();

        $variable = $this->getIsLocal()->getBool() ? $source->getVariable($name) : VariableRegistry::global()->get($name);
        if ($variable === null) {
            throw new InvalidFlowValueException($this->getName(), Language::get("variable.notFound", [$name]));
        }
        if (!($variable instanceof ListVariable)) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.addListVariable.error.existsOtherType", [$name, (string)$variable]));
        }

        foreach ($values as $value) {
            $addVariable = $helper->copyOrCreateVariable($value, $source->getVariableRegistryCopy());
            $variable->appendValue($addVariable);
        }

        yield Await::ALL;
    }

    public function getAddingVariables(): array {
        return [
            (string)$this->getVariableName() => new DummyVariable(ListVariable::class, "[".$this->getValue()."]")
        ];
    }
}