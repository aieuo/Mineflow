<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\math;

use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\base\SimpleCondition;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\libs\_ac618486ac522f0b\SOFe\AwaitGenerator\Await;

class RandomNumber extends SimpleCondition {

    public function __construct(int $min = null, int $max = null, int $value = null) {
        parent::__construct(self::RANDOM_NUMBER, FlowItemCategory::MATH);

        $this->setArguments([
            NumberArgument::create("min", $min)->example("0"),
            NumberArgument::create("max", $max)->example("10"),
            NumberArgument::create("value", $value)->example("0"),
        ]);
    }

    public function getMin(): NumberArgument {
        return $this->getArgument("min");
    }

    public function getMax(): NumberArgument {
        return $this->getArgument("max");
    }

    public function getValue(): NumberArgument {
        return $this->getArgument("value");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $min = $this->getMin()->getInt($source);
        $max = $this->getMax()->getInt($source);
        $value = $this->getValue()->getInt($source);

        yield Await::ALL;
        return mt_rand(min($min, $max), max($min, $max)) === $value;
    }
}