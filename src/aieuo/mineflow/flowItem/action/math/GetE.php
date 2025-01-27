<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\math;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\libs\_057384fe9e664697\SOFe\AwaitGenerator\Await;

class GetE extends TypeGetMathVariable {

    public function __construct(string $resultName = "e") {
        parent::__construct(self::GET_E, resultName: $resultName);
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $resultName = $this->getResultName()->getString($source);
        $source->addVariable($resultName, new NumberVariable(M_E));

        yield Await::ALL;
        return M_E;
    }
}