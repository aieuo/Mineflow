<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\variable;

use aieuo\mineflow\flowItem\argument\BooleanArgument;
use aieuo\mineflow\flowItem\argument\IsLocalVariableArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\Mineflow;
use SOFe\AwaitGenerator\Await;

class DeleteVariable extends SimpleAction {

    public function __construct(string $variableName = "", bool $isLocal = true) {
        parent::__construct(self::DELETE_VARIABLE, FlowItemCategory::VARIABLE);

        $this->setArguments([
            new StringArgument("name", $variableName, "@action.variable.form.name", example: "aieuo"),
            new IsLocalVariableArgument("scope", $isLocal),
        ]);
    }

    public function getVariableName(): StringArgument {
        return $this->getArguments()[0];
    }

    public function getIsLocal(): BooleanArgument {
        return $this->getArguments()[1];
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $name = $this->getVariableName()->getString($source);
        if ($this->getIsLocal()->getBool()) {
            $source->removeVariable($name);
        } else {
            Mineflow::getVariableHelper()->delete($name);
        }

        yield Await::ALL;
    }
}
