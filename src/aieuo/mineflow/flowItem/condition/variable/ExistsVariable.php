<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\variable;

use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleCondition;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\Mineflow;
use SOFe\AwaitGenerator\Await;

class ExistsVariable extends SimpleCondition {

    public function __construct(string $variableName = "") {
        parent::__construct(self::EXISTS_VARIABLE, FlowItemCategory::VARIABLE);

        $this->setArguments([
            StringArgument::create("name", $variableName, "@action.variable.form.name")->example("aieuo"),
        ]);
    }

    public function getVariableName(): StringArgument {
        return $this->getArguments()[0];
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $helper = Mineflow::getVariableHelper();
        $name = $this->getVariableName()->getString($source);

        yield Await::ALL;
        return $source->getVariable($name) !== null or $helper->get($name) !== null or $helper->getNested($name) !== null;
    }
}
