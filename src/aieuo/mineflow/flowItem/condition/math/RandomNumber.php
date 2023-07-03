<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\math;

use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\base\SimpleCondition;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use SOFe\AwaitGenerator\Await;

class RandomNumber extends SimpleCondition {

    public function __construct(int $min = null, int $max = null, int $value = null) {
        parent::__construct(self::RANDOM_NUMBER, FlowItemCategory::MATH);

        $this->setArguments([
            new NumberArgument("min", $min, example: "0"),
            new NumberArgument("max", $max, example: "10"),
            new NumberArgument("value", $value, example: "0"),
        ]);
    }

    public function getMin(): NumberArgument {
        return $this->getArguments()[0];
    }

    public function getMax(): NumberArgument {
        return $this->getArguments()[1];
    }

    public function getValue(): NumberArgument {
        return $this->getArguments()[2];
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $min = $this->getMin()->getInt($source);
        $max = $this->getMax()->getInt($source);
        $value = $this->getValue()->getInt($source);

        yield Await::ALL;
        return mt_rand(min($min, $max), max($min, $max)) === $value;
    }
}
