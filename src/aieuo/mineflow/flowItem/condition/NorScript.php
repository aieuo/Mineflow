<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\flowItem\FlowItemExecutor;

class NorScript extends ORScript {

    protected string $id = self::CONDITION_NOR;

    protected string $name = "condition.nor.name";
    protected string $detail = "condition.nor.detail";

    public function getDetail(): string {
        $details = ["-----------nor-----------"];
        foreach ($this->getConditions() as $condition) {
            $details[] = $condition->getShortDetail();
        }
        $details[] = "------------------------";
        return implode("\n", $details);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        return !(yield from parent::execute($source));
    }
}
