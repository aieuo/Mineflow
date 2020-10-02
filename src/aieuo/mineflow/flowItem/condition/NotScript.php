<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\flowItem\FlowItemContainer;

class NotScript extends NandScript {

    protected $id = self::CONDITION_NOT;

    protected $name = "condition.not.name";
    protected $detail = "condition.not.description";

    public function getDetail(): string {
        $details = ["-----------not-----------"];
        foreach ($this->getItems(FlowItemContainer::CONDITION) as $condition) {
            $details[] = $condition->getDetail();
        }
        $details[] = "------------------------";
        return implode("\n", $details);
    }
}