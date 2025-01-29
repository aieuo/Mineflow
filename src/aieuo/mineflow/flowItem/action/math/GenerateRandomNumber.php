<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\math;

use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\libs\_3ced88d4028c9717\SOFe\AwaitGenerator\Await;

class GenerateRandomNumber extends TypeGetMathVariable {

    public function __construct(string $min = "", string $max = "", string $resultName = "random") {
        parent::__construct(self::GENERATE_RANDOM_NUMBER, resultName: $resultName);

        $this->setArguments([
            NumberArgument::create("min", $min)->example("0"),
            NumberArgument::create("max", $max)->example("10"),
            StringArgument::create("result", $resultName, "@action.form.resultVariableName")->example("random"),
        ]);
    }

    public function getMin(): NumberArgument {
        return $this->getArgument("min");
    }

    public function getMax(): NumberArgument {
        return $this->getArgument("max");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $min = $this->getMin()->getInt($source);
        $max = $this->getMax()->getInt($source);
        $resultName = $this->getResultName()->getString($source);

        $rand = mt_rand($min, $max);
        $source->addVariable($resultName, new NumberVariable($rand));

        yield Await::ALL;
        return $rand;
    }
}