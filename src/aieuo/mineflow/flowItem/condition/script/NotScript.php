<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\script;

use aieuo\mineflow\flowItem\FlowItemExecutor;

class NotScript extends LogicalOperation {

    public function __construct() {
        parent::__construct(self::CONDITION_NOT);
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        foreach ($this->getConditions() as $condition) {
            if (!(yield from $condition->execute($source))) return true;
        }
        return false;
    }
}