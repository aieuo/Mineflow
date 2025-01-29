<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\variable;

use aieuo\mineflow\flowItem\argument\BooleanArgument;
use aieuo\mineflow\flowItem\argument\IsLocalVariableArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\variable\registry\VariableRegistry;
use aieuo\mineflow\libs\_ddbd776e705b8daa\SOFe\AwaitGenerator\Await;

class DeleteVariable extends SimpleAction {

    public function __construct(string $variableName = "", bool $isLocal = true) {
        parent::__construct(self::DELETE_VARIABLE, FlowItemCategory::VARIABLE);

        $this->setArguments([
            StringArgument::create("name", $variableName, "@action.variable.form.name")->example("aieuo"),
            IsLocalVariableArgument::create("scope", $isLocal),
        ]);
    }

    public function getVariableName(): StringArgument {
        return $this->getArgument("name");
    }

    public function getIsLocal(): BooleanArgument {
        return $this->getArgument("scope");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $name = $this->getVariableName()->getString($source);
        if ($this->getIsLocal()->getBool()) {
            $source->removeVariable($name);
        } else {
            VariableRegistry::global()->remove($name);
        }

        yield Await::ALL;
    }
}