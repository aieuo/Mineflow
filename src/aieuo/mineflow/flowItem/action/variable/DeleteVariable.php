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

    private StringArgument $variableName;
    private BooleanArgument $isLocal;

    public function __construct(string $variableName = "", bool $isLocal = true) {
        parent::__construct(self::DELETE_VARIABLE, FlowItemCategory::VARIABLE);

        $this->setArguments([
            $this->variableName = new StringArgument("name", $variableName, "@action.variable.form.name", example: "aieuo"),
            $this->isLocal = new IsLocalVariableArgument("scope", $isLocal),
        ]);
    }

    public function getVariableName(): StringArgument {
        return $this->variableName;
    }

    public function getIsLocal(): BooleanArgument {
        return $this->isLocal;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $name = $this->variableName->getString($source);
        if ($this->isLocal->getBool()) {
            $source->removeVariable($name);
        } else {
            Mineflow::getVariableHelper()->delete($name);
        }

        yield Await::ALL;
    }
}
