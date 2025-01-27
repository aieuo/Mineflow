<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\common;

use aieuo\mineflow\flowItem\base\SimpleCondition;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\libs\_057384fe9e664697\SOFe\AwaitGenerator\Await;

class CheckNothing extends SimpleCondition {

    public function __construct() {
        parent::__construct(self::CHECK_NOTHING, FlowItemCategory::COMMON);
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        yield Await::ALL;
        return true;
    }
}