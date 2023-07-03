<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\variable;

use aieuo\mineflow\flowItem\argument\BooleanArgument;
use aieuo\mineflow\flowItem\argument\IsLocalVariableArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleCondition;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\variable\ListVariable;
use SOFe\AwaitGenerator\Await;

class ExistsListVariableKey extends SimpleCondition {

    public function __construct(string $variableName = "", string $variableKey = "", bool $isLocal = true) {
        parent::__construct(self::EXISTS_LIST_VARIABLE_KEY, FlowItemCategory::VARIABLE);

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

        $variable = $this->getIsLocal()->getBool() ? $source->getVariable($name) : $helper->get($name);
        if (!($variable instanceof ListVariable)) return false;
        $value = $variable->getValue();

        yield Await::ALL;
        return isset($value[$key]);
    }
}
