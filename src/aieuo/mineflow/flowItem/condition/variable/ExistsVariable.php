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

    private StringArgument $variableName;

    public function __construct(string $variableName = "") {
        parent::__construct(self::EXISTS_VARIABLE, FlowItemCategory::VARIABLE);

        $this->setArguments([
            $this->variableName = new StringArgument("name", $variableName, "@action.variable.form.name", example: "aieuo"),
        ]);
    }

    public function getVariableName(): StringArgument {
        return $this->variableName;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $helper = Mineflow::getVariableHelper();
        $name = $this->variableName->getString($source);

        yield Await::ALL;
        return $source->getVariable($name) !== null or $helper->get($name) !== null or $helper->getNested($name) !== null;
    }
}
