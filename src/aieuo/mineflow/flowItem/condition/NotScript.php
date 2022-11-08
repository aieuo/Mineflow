<?php

namespace aieuo\mineflow\flowItem\condition;

class NotScript extends NandScript {

    protected string $id = self::CONDITION_NOT;

    protected string $name = "condition.not.name";
    protected string $detail = "condition.not.detail";

    public function getDetail(): string {
        $details = ["-----------not-----------"];
        foreach ($this->getConditions() as $condition) {
            $details[] = $condition->getShortDetail();
        }
        $details[] = "------------------------";
        return implode("\n", $details);
    }
}
