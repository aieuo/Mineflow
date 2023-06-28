<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\base;

use aieuo\mineflow\flowItem\argument\FlowItemArgument;
use aieuo\mineflow\flowItem\condition\Condition;
use function array_map;

abstract class SimpleCondition extends SimpleFlowItem implements Condition {
    use ConditionNameWithMineflowLanguage;

    public function getDetailDefaultReplaces(): array {
        return array_map(fn(FlowItemArgument $value) => $value->getName(), $this->getArguments());
    }

    public function getDetailReplaces(): array {
        return array_map(fn(FlowItemArgument $value) => $value->get(), $this->getArguments());
    }
}