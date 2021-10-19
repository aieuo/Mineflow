<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\math;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\variable\NumberVariable;

class GetPi extends TypeGetMathVariable {

    protected string $id = self::GET_PI;

    protected string $name = "action.getPi.name";
    protected string $detail = "action.getPi.detail";

    protected string $resultName = "pi";

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $resultName = $source->replaceVariables($this->getResultName());
        $source->addVariable($resultName, new NumberVariable(M_PI));
        yield FlowItemExecutor::CONTINUE;
        return M_PI;
    }
}