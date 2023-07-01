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
use aieuo\mineflow\variable\ListVariable;
use SOFe\AwaitGenerator\Await;

class DeleteListVariableContent extends SimpleAction {

    private StringArgument $variableName;
    private StringArgument $variableKey;
    private BooleanArgument $isLocal;

    public function __construct(string $variableName = "", string $variableKey = "", bool $isLocal = true) {
        parent::__construct(self::DELETE_LIST_VARIABLE_CONTENT, FlowItemCategory::VARIABLE);

        $this->setArguments([
            $this->variableName = new StringArgument("name", $variableName, "@action.variable.form.name", example: "aieuo"),
            $this->variableKey = new StringArgument("key", $variableKey, "@action.variable.form.key", example: "auieo"),
            $this->isLocal = new IsLocalVariableArgument("scope", $isLocal),
        ]);
    }

    public function getVariableName(): StringArgument {
        return $this->variableName;
    }

    public function getVariableKey(): StringArgument {
        return $this->variableKey;
    }

    public function getIsLocal(): BooleanArgument {
        return $this->isLocal;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $helper = Mineflow::getVariableHelper();
        $name = $this->variableName->getString($source);
        $key = $this->variableKey->getString($source);

        $variable = ($this->isLocal->getBool() ? $source->getVariable($name) : $helper->getNested($name));
        if ($variable === null) {
            throw new InvalidFlowValueException($this->getName(), Language::get("variable.notFound", [$name]));
        }
        if (!($variable instanceof ListVariable)) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.addListVariable.error.existsOtherType", [$name, (string)$variable]));
        }

        $variable->removeValueAt($key);

        yield Await::ALL;
    }
}
