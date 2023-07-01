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
use SOFe\AwaitGenerator\Await;
use function implode;

class AddListVariable extends SimpleAction {

    private StringArgument $variableName;
    private StringArrayArgument $value;
    private BooleanArgument $isLocal;

    public function __construct(
        string $variableName = "",
        string $value = "",
        bool   $isLocal = true
    ) {
        parent::__construct(self::ADD_LIST_VARIABLE, FlowItemCategory::VARIABLE);

        $this->setArguments([
            $this->variableName = new StringArgument("name", $variableName, "@action.variable.form.name", example: "aieuo"),
            $this->value = new StringArrayArgument("value", $value, "@action.variable.form.value", example: "aiueo"),
            $this->isLocal = new IsLocalVariableArgument("scope", $isLocal),
        ]);
    }

    public function getVariableName(): StringArgument {
        return $this->variableName;
    }

    public function getValue(): StringArrayArgument {
        return $this->value;
    }

    public function getIsLocal(): BooleanArgument {
        return $this->isLocal;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $helper = Mineflow::getVariableHelper();
        $name = $this->variableName->getString($source);
        $values = $this->value->getRawArray();

        $variable = $this->isLocal->getBool() ? $source->getVariable($name) : $helper->get($name);
        if ($variable === null) {
            throw new InvalidFlowValueException($this->getName(), Language::get("variable.notFound", [$name]));
        }
        if (!($variable instanceof ListVariable)) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.addListVariable.error.existsOtherType", [$name, (string)$variable]));
        }

        foreach ($values as $value) {
            $addVariable = $helper->copyOrCreateVariable($value, $source);
            $variable->appendValue($addVariable);
        }

        yield Await::ALL;
    }

    public function getAddingVariables(): array {
        return [
            $this->variableName->get() => new DummyVariable(ListVariable::class, "[".implode(",", $this->value->get())."]")
        ];
    }
}
