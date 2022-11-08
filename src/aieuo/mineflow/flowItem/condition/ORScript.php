<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\flowItem\FlowItemExecutor;

class ORScript extends AndScript {

    protected string $id = self::CONDITION_OR;

    protected string $name = "condition.or.name";
    protected string $detail = "condition.or.detail";

    public function getDetail(): string {
        $details = ["-----------or-----------"];
        foreach ($this->getConditions() as $condition) {
            $details[] = $condition->getShortDetail();
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
