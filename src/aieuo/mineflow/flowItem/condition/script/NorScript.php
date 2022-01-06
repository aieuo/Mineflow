<?php

namespace aieuo\mineflow\flowItem\condition\script;

use aieuo\mineflow\flowItem\FlowItemExecutor;

class NorScript extends LogicalOperation {

    protected string $name = "condition.nor.name";
    protected string $detail = "condition.nor.detail";

    public function __construct() {
        parent::__construct(self::CONDITION_NOR);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        foreach ($this->getConditions() as $condition) {
            if (yield from $condition->execute($source)) return false;
        }
        return true;
    }
}