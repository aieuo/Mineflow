<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\math;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\variable\NumberVariable;
use SOFe\AwaitGenerator\Await;

class GetE extends TypeGetMathVariable {

    public function __construct(string $resultName = "e") {
        parent::__construct(self::GET_E, resultName: $resultName);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $resultName = $source->replaceVariables($this->getResultName());
        $source->addVariable($resultName, new NumberVariable(M_E));

        yield Await::ALL;
        return M_E;
    }
}
