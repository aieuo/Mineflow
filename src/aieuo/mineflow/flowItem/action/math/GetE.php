<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\math;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\variable\NumberVariable;

class GetE extends TypeGetMathVariable {

    protected $id = self::GET_E;

    protected $name = "action.getE.name";
    protected $detail = "action.getE.detail";

    /** @var string */
    protected $resultName = "e";

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $resultName = $source->replaceVariables($this->getResultName());
        $source->addVariable($resultName, new NumberVariable(M_E));
        yield true;
        return M_E;
    }
}