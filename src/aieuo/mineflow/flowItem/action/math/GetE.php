<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\math;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\variable\NumberVariable;

class GetE extends TypeGetMathVariable {

    protected string $name = "action.getE.name";
    protected string $detail = "action.getE.detail";

    public function __construct(string $resultName = "e") {
        parent::__construct(self::GET_E, resultName: $resultName);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $resultName = $source->replaceVariables($this->getResultName());
        $source->addVariable($resultName, new NumberVariable(M_E));
        yield true;
        return M_E;
    }
}