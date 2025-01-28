<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\base;

use aieuo\mineflow\flowItem\argument\FlowItemArgument;
use aieuo\mineflow\flowItem\FlowItem;
use function array_map;

abstract class SimpleFlowItem extends FlowItem {
    use NameWithMineflowLanguage;

    public function getMessageKey(): string {
        return $this->getId();
    }

    public function getDetailDefaultReplaces(): array {
        return array_map(fn(FlowItemArgument $value) => $value->getName(), $this->getArguments());
    }

    public function getDetailReplaces(): array {
        return array_map(fn(FlowItemArgument $value) => (string)$value, $this->getArguments());
    }
}