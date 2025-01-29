<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\base;

use aieuo\mineflow\flowItem\argument\FlowItemArgument;
use aieuo\mineflow\flowItem\condition\Condition;
use aieuo\mineflow\flowItem\FlowItem;
use function array_map;

abstract class SimpleAction extends SimpleFlowItem {
    public function getMessageKeyPrefix(): string {
        return "action";
    }
}