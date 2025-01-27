<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\script;

use aieuo\mineflow\flowItem\FlowItemExecutor;

class ORScript extends LogicalOperation {

    public function __construct(string $id = self::CONDITION_OR) {
        parent::__construct($id);
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        foreach ($this->getConditions() as $condition) {
            if (yield from $condition->execute($source)) return true;
        }
        return false;
    }
}