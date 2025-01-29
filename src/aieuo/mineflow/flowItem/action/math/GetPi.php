<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\math;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\libs\_3ced88d4028c9717\SOFe\AwaitGenerator\Await;

class GetPi extends TypeGetMathVariable {

    public function __construct(string $resultName = "pi") {
        parent::__construct(self::GET_PI, resultName: $resultName);
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $resultName = $this->getResultName()->getString($source);
        $source->addVariable($resultName, new NumberVariable(M_PI));

        yield Await::ALL;
        return M_PI;
    }
}