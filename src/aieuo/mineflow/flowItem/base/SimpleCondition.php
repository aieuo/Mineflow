<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\base;

use aieuo\mineflow\flowItem\condition\Condition;
use aieuo\mineflow\flowItem\placeholder\Placeholder;
use function array_map;

abstract class SimpleCondition extends SimpleFlowItem implements Condition {
    use ConditionNameWithMineflowLanguage;

    public function getDetailDefaultReplaces(): array {
        return array_map(fn(Placeholder $value) => $value->getName(), $this->getPlaceholders());
    }

    public function getDetailReplaces(): array {
        return array_map(fn(Placeholder $value) => $value->get(), $this->getPlaceholders());
    }
}
