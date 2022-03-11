<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\flowItem\FlowItemExecutor;

class NandScript extends LogicalOperation {

    protected string $name = "condition.nand.name";
    protected string $detail = "condition.nand.detail";

    public function __construct() {
        parent::__construct(self::CONDITION_NAND);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        foreach ($this->getConditions() as $condition) {
            if (!(yield from $condition->execute($source))) return true;
        }
        return false;
    }
}