<?php

namespace aieuo\mineflow\flowItem\condition;

class NotScript extends NandScript {

    protected $id = self::CONDITION_NOT;

    protected $name = "condition.not.name";
    protected $detail = "condition.not.description";

    public function getDetail(): string {
        $details = ["-----------not-----------"];
        foreach ($this->getConditions() as $condition) {
            $details[] = $condition->getDetail();
        }
        $details[] = "------------------------";
        return implode("\n", $details);
    }
}