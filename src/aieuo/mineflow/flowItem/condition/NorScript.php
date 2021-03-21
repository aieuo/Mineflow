<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\flowItem\FlowItemExecutor;

class NorScript extends ORScript {

    protected $id = self::CONDITION_NOR;

    protected $name = "condition.nor.name";
    protected $detail = "condition.nor.detail";

    public function getDetail(): string {
        $details = ["-----------nor-----------"];
        foreach ($this->getConditions() as $condition) {
            $details[] = $condition->getDetail();
        }
        $details[] = "------------------------";
        return implode("\n", $details);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        return !(yield from parent::execute($source));
    }
}