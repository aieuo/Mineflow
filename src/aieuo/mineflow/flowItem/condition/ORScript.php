<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\flowItem\FlowItemExecutor;

class ORScript extends AndScript {

    protected $id = self::CONDITION_OR;

    protected $name = "condition.or.name";
    protected $detail = "condition.or.detail";

    public function getDetail(): string {
        $details = ["-----------or-----------"];
        foreach ($this->getConditions() as $condition) {
            $details[] = $condition->getDetail();
        }
        $details[] = "------------------------";
        return implode("\n", $details);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        foreach ($this->getConditions() as $condition) {
            if (yield from $condition->execute($source)) return true;
        }
        return false;
    }
}