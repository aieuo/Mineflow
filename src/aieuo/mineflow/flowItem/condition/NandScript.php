<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\flowItem\FlowItemExecutor;

class NandScript extends AndScript {

    protected string $id = self::CONDITION_NAND;

    protected string $name = "condition.nand.name";
    protected string $detail = "condition.nand.detail";

    public function getDetail(): string {
        $details = ["-----------nand-----------"];
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