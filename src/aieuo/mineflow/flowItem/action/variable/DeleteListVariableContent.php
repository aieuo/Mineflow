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

    public function __construct(string $variableName = "", string $variableKey = "", bool $isLocal = true) {
        parent::__construct(self::DELETE_LIST_VARIABLE_CONTENT, FlowItemCategory::VARIABLE);

        $this->setArguments([
            new StringArgument("name", $variableName, "@action.variable.form.name", example: "aieuo"),
            new StringArgument("key", $variableKey, "@action.variable.form.key", example: "auieo"),
            new IsLocalVariableArgument("scope", $isLocal),
        ]);
    }

    public function getVariableName(): StringArgument {
        return $this->getArguments()[0];
    }

    public function getVariableKey(): StringArgument {
        return $this->getArguments()[1];
    }

    public function getIsLocal(): BooleanArgument {
        return $this->getArguments()[2];
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $helper = Mineflow::getVariableHelper();
        $name = $this->getVariableName()->getString($source);
        $key = $this->getVariableKey()->getString($source);

        $variable = ($this->getIsLocal()->getBool() ? $source->getVariable($name) : $helper->getNested($name));
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
