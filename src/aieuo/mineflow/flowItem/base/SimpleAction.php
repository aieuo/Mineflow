<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\base;

use aieuo\mineflow\flowItem\argument\FlowItemArgument;
use function array_map;

abstract class SimpleAction extends SimpleFlowItem {
    use ActionNameWithMineflowLanguage;

    public function getDetailDefaultReplaces(): array {
        return array_map(fn(FlowItemArgument $value) => $value->getName(), $this->getArguments());
    }

    public function getDetailReplaces(): array {
        return array_map(fn(FlowItemArgument $value) => (string)$value, $this->getArguments());
    }
}
