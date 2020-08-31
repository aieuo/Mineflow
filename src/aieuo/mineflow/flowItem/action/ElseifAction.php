<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\recipe\Recipe;

class ElseifAction extends IFAction {

    protected $id = self::ACTION_ELSEIF;

    protected $name = "action.elseif.name";
    protected $detail = "action.elseif.description";

    public function getDetail(): string {
        $details = ["=============elseif============="];
        foreach ($this->getConditions() as $condition) {
            $details[] = $condition->getDetail();
        }
        $details[] = "~~~~~~~~~~~~~~~~~~~~~~~~~~~";
        foreach ($this->getActions() as $action) {
            $details[] = $action->getDetail();
        }
        $details[] = "================================";
        return implode("\n", $details);
    }

    public function execute(Recipe $origin) {
        $lastResult = $this->getParent()->getLastActionResult();
        if ($lastResult === null) throw new InvalidFlowValueException();
        if ($lastResult) return true;

        foreach ($this->getConditions() as $condition) {
            if (!$condition->execute($origin)) return false;
        }

        yield from $this->executeActions($origin);
        return true;
    }
}