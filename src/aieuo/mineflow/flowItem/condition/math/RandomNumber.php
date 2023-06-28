<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\math;

use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\base\SimpleCondition;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use SOFe\AwaitGenerator\Await;

class RandomNumber extends SimpleCondition {

    private NumberArgument $min;
    private NumberArgument $max;
    private NumberArgument $value;

    public function __construct(int $min = null, int $max = null, int $value = null) {
        parent::__construct(self::RANDOM_NUMBER, FlowItemCategory::MATH);

        $this->setArguments([
            $this->min = new NumberArgument("min", $min, example: "0"),
            $this->max = new NumberArgument("max", $max, example: "10"),
            $this->value = new NumberArgument("value", $value, example: "0"),
        ]);
    }

    public function getMin(): NumberArgument {
        return $this->min;
    }

    public function getMax(): NumberArgument {
        return $this->max;
    }

    public function getValue(): NumberArgument {
        return $this->value;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $min = $this->min->getInt($source);
        $max = $this->max->getInt($source);
        $value = $this->value->getInt($source);

        yield Await::ALL;
        return mt_rand(min($min, $max), max($min, $max)) === $value;
    }
}
