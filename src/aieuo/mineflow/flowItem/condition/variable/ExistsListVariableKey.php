<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\variable;

use aieuo\mineflow\flowItem\argument\BooleanArgument;
use aieuo\mineflow\flowItem\argument\IsLocalVariableArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleCondition;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\variable\IteratorVariable;
use aieuo\mineflow\variable\registry\VariableRegistry;
use aieuo\mineflow\libs\_ac618486ac522f0b\SOFe\AwaitGenerator\Await;

class ExistsListVariableKey extends SimpleCondition {

    public function __construct(string $variableName = "", string $variableKey = "", bool $isLocal = true) {
        parent::__construct(self::EXISTS_LIST_VARIABLE_KEY, FlowItemCategory::VARIABLE);

        $this->setArguments([
            StringArgument::create("name", $variableName, "@action.variable.form.name")->example("aieuo"),
            StringArgument::create("key", $variableKey, "@action.variable.form.key")->example("auieo"),
            IsLocalVariableArgument::create("scope", $isLocal),
        ]);
    }

    public function getVariableName(): StringArgument {
        return $this->getArgument("name");
    }

    public function getVariableKey(): StringArgument {
        return $this->getArgument("key");
    }

    public function getIsLocal(): BooleanArgument {
        return $this->getArgument("scope");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $name = $this->getVariableName()->getString($source);
        $key = $this->getVariableKey()->getString($source);

        $variable = $this->getIsLocal()->getBool() ? $source->getVariable($name) : VariableRegistry::global()->get($name);
        if (!($variable instanceof IteratorVariable)) return false;

        yield Await::ALL;
        return $variable->hasKey($key);
    }
}