<?php

namespace aieuo\mineflow\flowItem\condition\script;

use aieuo\mineflow\flowItem\FlowItemExecutor;

class NotScript extends LogicalOperation {

    protected string $name = "condition.not.name";
    protected string $detail = "condition.not.detail";

    public function __construct() {
        parent::__construct(self::CONDITION_NOT);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        foreach ($this->getConditions() as $condition) {
            if (!(yield from $condition->execute($source))) return true;
        }
        return false;
    }
}