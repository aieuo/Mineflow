<?php

namespace aieuo\mineflow\flowItem\condition\script;

use aieuo\mineflow\flowItem\FlowItemExecutor;

class AndScript extends LogicalOperation {

    protected string $name = "condition.and.name";
    protected string $detail = "condition.and.detail";

    public function __construct() {
        parent::__construct(self::CONDITION_AND);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        foreach ($this->getConditions() as $condition) {
            if (!(yield from $condition->execute($source))) return false;
        }
        return true;
    }
}
