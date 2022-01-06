<?php

namespace aieuo\mineflow\flowItem\condition\script;

use aieuo\mineflow\flowItem\FlowItemExecutor;

class ORScript extends LogicalOperation {

    protected string $name = "condition.or.name";
    protected string $detail = "condition.or.detail";

    public function __construct(string $id = self::CONDITION_OR) {
        parent::__construct($id);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        foreach ($this->getConditions() as $condition) {
            if (yield from $condition->execute($source)) return true;
        }
        return false;
    }
}