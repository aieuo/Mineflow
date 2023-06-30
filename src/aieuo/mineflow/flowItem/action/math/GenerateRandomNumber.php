<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\math;

use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\variable\NumberVariable;
use SOFe\AwaitGenerator\Await;

class GenerateRandomNumber extends TypeGetMathVariable {

    private NumberArgument $min;
    private NumberArgument $max;

    public function __construct(string $min = "", string $max = "", string $resultName = "random") {
        parent::__construct(self::GENERATE_RANDOM_NUMBER, resultName: $resultName);

        $this->setArguments([
            $this->min = new NumberArgument("min", $min, example: "0"),
            $this->max = new NumberArgument("max", $max, example: "10"),
            $this->resultName = new StringArgument("result", $resultName, "@action.form.resultVariableName", example: "random"),
        ]);
    }

    public function getMin(): NumberArgument {
        return $this->min;
    }

    public function getMax(): NumberArgument {
        return $this->max;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $min = $this->min->getInt($source);
        $max = $this->max->getInt($source);
        $resultName = $this->resultName->getString($source);

        $rand = mt_rand($min, $max);
        $source->addVariable($resultName, new NumberVariable($rand));

        yield Await::ALL;
        return $rand;
    }
}
