<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\variable\NumberVariable;

class GetPi extends TypeGetMathVariable {

    protected $id = self::GET_PI;

    protected $name = "action.getPi.name";
    protected $detail = "action.getPi.detail";

    protected $resultName = "pi";

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $resultName = $source->replaceVariables($this->getResultName());
        $source->addVariable($resultName, new NumberVariable(M_PI));
        yield true;
        return M_PI;
    }
}