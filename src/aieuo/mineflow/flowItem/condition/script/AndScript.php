<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\script;

use aieuo\mineflow\flowItem\FlowItemExecutor;

class AndScript extends LogicalOperation {

    public function __construct() {
        parent::__construct(self::CONDITION_AND);
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        foreach ($this->getConditions() as $condition) {
            if (!(yield from $condition->execute($source))) return false;
        }
        return true;
    }
}